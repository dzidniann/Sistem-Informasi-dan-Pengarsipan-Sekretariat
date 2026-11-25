"""HTTP smoke tests for an isolated LOCAL production-image project only.

Usage: python tests/Deployment/production_smoke.py ENV_FILE BASE_URL PROJECT
Never accepts office/remote hosts or the development port 8001.
"""
import html
import http.cookiejar
import json
import re
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request
import uuid
from pathlib import Path

root = Path(__file__).resolve().parents[2]
env_file, base, project = sys.argv[1:]
url = urllib.parse.urlsplit(base)
assert url.hostname in ("127.0.0.1", "localhost") and url.port not in (None, 8001, 8500)
assert project.startswith("simarta-smoke-")
env = dict(line.split("=", 1) for line in Path(env_file).read_text().splitlines() if "=" in line and not line.startswith("#"))
assert env["DB_DATABASE"] == "simarta_smoke" and env["COMPOSE_PROJECT_NAME"] == project
dc = ["docker", "compose", "--project-directory", str(root), "--env-file", env_file, "-f", str(root / "compose.production.yml"), "-p", project]


def php(code):
    bootstrap = "<?php require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); if(config('database.connections.mysql.database')!=='simarta_smoke')exit(90); "
    p = subprocess.run(dc + ["exec", "-T", "app", "php"], input=bootstrap + code, text=True, capture_output=True)
    if p.returncode:
        raise RuntimeError("Fixture/check failed; inspect isolated container logs")
    return p.stdout.strip()


php(r"""
foreach(['super-admin','sekretariat','staf-bidang','legal-admin','humas'] as $name){
 $role=App\Models\Role::firstOrCreate(['nama'=>$name]);
 $bidang=App\Models\Bidang::firstOrCreate(['nama_bidang'=>$name==='sekretariat'?'Sekretariat dan Pengarsipan':$name]);
 App\Models\User::updateOrCreate(['email'=>$name.'@smoke.invalid'],['name'=>'Smoke '.$name,'password'=>bcrypt(getenv('SMOKE_PASSWORD')),'role_id'=>$role->id,'bidang_id'=>$bidang->id,'is_active'=>true]);
}
echo 'fixture-ready';
""")


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args):
        return None


class Client:
    def __init__(self):
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()), NoRedirect())

    def request(self, path, data=None, headers=None):
        address = urllib.parse.urljoin(base + "/", path)
        target = urllib.parse.urlsplit(address)
        assert target.scheme == url.scheme and target.netloc == url.netloc, "Unexpected redirect scheme/host"
        req = urllib.request.Request(address, data=data, headers=headers or {})
        try:
            response = self.opener.open(req, timeout=150)
        except urllib.error.HTTPError as response_error:
            response = response_error
        return response.status, response.headers, response.read()

    def token(self, path):
        status, _, body = self.request(path)
        assert status == 200, (path, status)
        match = re.search(rb'name="_token"\s+value="([^"]+)"', body)
        assert match, "Missing CSRF token"
        return html.unescape(match[1].decode())

    def login(self, role):
        token = self.token("/login")
        status, headers, _ = self.request("/login", urllib.parse.urlencode({"_token": token, "email": role + "@smoke.invalid", "password": env["SMOKE_PASSWORD"]}).encode(), {"Content-Type": "application/x-www-form-urlencoded"})
        assert status == 302 and "/login" not in headers["Location"], (role, status)
        status, _, _ = self.request(headers["Location"])
        assert status == 200, (role, status)
        print("PASS login/dashboard", role)

    def post(self, path, fields, files=()):
        boundary = "Smoke" + uuid.uuid4().hex
        chunks = []
        for key, value in fields.items():
            chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
        for key, name, mime, content in files:
            chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode() + content + b"\r\n")
        chunks.append(f"--{boundary}--\r\n".encode())
        return self.request(path, b"".join(chunks), {"Content-Type": "multipart/form-data; boundary=" + boundary})


guest = Client()
login_body = guest.request('/login')[2].decode()
asset_urls = set(re.findall(r'(?:href|src)="([^"]*/build/assets/[^"]+)"', login_body))
assert asset_urls and 'localhost:5173' not in login_body and '@vite/client' not in login_body
for asset in asset_urls:
    status, _, content = guest.request(asset)
    assert status == 200 and len(content) > 100
print('PASS production CSS/JS assets served without Vite dev server')
assert guest.request("/register")[0] == 404
assert guest.request("/storage/private-probe.pdf")[0] == 302
assert guest.request("/.env")[0] in (403, 404)
for role in ("super-admin", "sekretariat", "staf-bidang", "legal-admin", "humas"):
    Client().login(role)

# Generate a real PDF using the shipped PDF engine, not a user's document.
pdf_hex = php(r"echo bin2hex(Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>SIMARTA smoke fixture</h1>')->output());")
pdf = bytes.fromhex(pdf_hex)
assert pdf.startswith(b"%PDF")
png = bytes.fromhex(php("$i=imagecreatetruecolor(8,8); ob_start(); imagepng($i); echo bin2hex(ob_get_clean());"))
assert png.startswith(b"\x89PNG")
legal = Client()
legal.login("legal-admin")
status, headers, _ = legal.post("/legal/governance-documents", {"_token": legal.token("/legal/governance-documents/create"), "judul_file": "Smoke governance", "tanggal": "2026-11-10"}, [("file", "smoke-governance.pdf", "application/pdf", pdf)])
assert status == 302 and "/governance-documents/" in headers["Location"], ("upload", status)
record = json.loads(php(r"$d=App\Models\LegalGovernanceDocument::where('judul_file','Smoke governance')->latest('id')->firstOrFail(); echo json_encode(['id'=>$d->id,'path'=>$d->file_path,'hash'=>hash_file('sha256',storage_path('app/'.$d->file_path))]);"))
assert Path(record["path"]).name.startswith(str(record["id"]) + "-") or Path(record["path"]).name.startswith(str(record["id"]) + "_")
for suffix, disposition in (("", "inline"), ("?download=1", "attachment")):
    status, headers, body = legal.request(f'/legal/governance-documents/{record["id"]}/file' + suffix)
    assert status == 200 and body.startswith(b"%PDF") and disposition in headers.get("Content-Disposition", "")
assert guest.request(f'/legal/governance-documents/{record["id"]}/file')[0] == 302
staff = Client()
staff.login("staf-bidang")
assert staff.request(f'/legal/governance-documents/{record["id"]}/file')[0] in (403, 404)
print("PASS PDF upload/optimization, ID filename, preview/download, access control")

secretariat = Client()
secretariat.login("sekretariat")
status, _, _ = secretariat.post("/notulensi", {"_token": secretariat.token("/notulensi"), "judul_rapat": "Smoke minutes", "tanggal_rapat": "2026-11-10", "notulis": "Smoke user"}, [("file", "smoke-minutes.pdf", "application/pdf", pdf), ("foto", "smoke-photo.png", "image/png", png)])
assert status == 302
minutes_id = php("echo App\\Models\\Notulensi::where('judul_rapat','Smoke minutes')->latest('id')->firstOrFail()->id;")
assert secretariat.request(f"/notulensi/{minutes_id}/berkas")[2].startswith(b"%PDF")
photo = php("echo App\\Models\\Notulensi::findOrFail(" + minutes_id + ")->foto_path;")
assert secretariat.request("/storage/" + photo)[2].startswith(b"\x89PNG")
assert guest.request("/storage/" + photo)[0] == 302
print("PASS secretariat minutes PDF/photo upload, GD processing and authorized preview")

status, _, _ = legal.post("/legal/activities", {"_token": legal.token("/legal/activities/create"), "tanggal_kegiatan": "2026-11-10", "uraian_kegiatan": "Smoke monthly activity"})
assert status == 302
for path in ("/legal/reports/monthly/preview?bulan=11&tahun=2026", "/legal/reports/monthly/pdf?bulan=11&tahun=2026"):
    status, _, body = legal.request(path)
    assert status == 200 and body.startswith(b"%PDF"), (path, status)
print("PASS monthly activity/report preview/download PDF")
humas = Client()
humas.login("humas")
for path, signature in (("/humas/reports/excel?bulan=11&tahun=2026", b"PK"), ("/humas/reports/pdf?bulan=11&tahun=2026", b"%PDF")):
    status, _, body = humas.request(path)
    assert status == 200 and body.startswith(signature), (path, status)
print("PASS CSR Excel and PDF exports")

status_ids = json.loads(php(r"""
$category=App\Models\LegalCategory::firstOrCreate(['name'=>'Smoke scheduler']);
$author=App\Models\User::where('email','legal-admin@smoke.invalid')->firstOrFail();
$ids=[];
foreach(['EXPIRED'=>[now()->subDay(),null], 'EXPIRING_SOON'=>[now()->addDays(10),null], 'NEED_REVIEW'=>[now()->addMonths(2),now()]] as $expected=>$dates){
 $ids[$expected]=Illuminate\Support\Facades\DB::table('legal_documents')->insertGetId(['name'=>'Smoke status '.$expected,'category_id'=>$category->id,'created_by'=>$author->id,'status'=>'ACTIVE','file_path'=>'smoke-status-only.pdf','expiry_date'=>$dates[0]->toDateString(),'review_date'=>$dates[1]?->toDateString(),'created_at'=>now(),'updated_at'=>now()]);
}
echo json_encode($ids);
"""))
subprocess.run(dc + ["exec", "-T", "app", "php", "artisan", "legal:refresh-document-statuses"], check=True, stdout=subprocess.DEVNULL)
for expected, doc_id in status_ids.items():
    assert php(f"echo App\\Models\\LegalDocument::findOrFail({int(doc_id)})->status;") == expected
subprocess.run(dc + ["exec", "-T", "app", "php", "artisan", "schedule:list"], check=True, stdout=subprocess.DEVNULL)
Path(env_file).with_name("smoke-record.json").write_text(json.dumps(record))
print("PASS scheduler registration and automatic EXPIRED/EXPIRING_SOON/NEED_REVIEW; checksum saved")
