"""Exercise sanitized forwarding with a temporary exact-peer QA map only."""
import ipaddress
import json
import re
import subprocess
import sys
import urllib.request
from pathlib import Path

root = Path(__file__).resolve().parents[2]
env_file = Path(sys.argv[1]).resolve()
values = dict(line.split("=", 1) for line in env_file.read_text().splitlines() if "=" in line and not line.startswith("#"))
project = values["COMPOSE_PROJECT_NAME"]
assert project.startswith("simarta-smoke-") and values["DB_DATABASE"] == "simarta_smoke"
assert values["WEB_BIND_IP"] == "127.0.0.1" and values["WEB_PORT"] not in ("8001", "8500")
base = "http://127.0.0.1:" + values["WEB_PORT"]
dc = ["docker", "compose", "--project-directory", str(root), "--env-file", str(env_file), "-p", project, "-f", str(root / "compose.production.yml")]


def login_html():
    req = urllib.request.Request(base + "/login", headers={"X-Forwarded-Proto": "https", "X-Forwarded-Host": "evil.invalid"})
    return urllib.request.urlopen(req, timeout=30).read().decode()


body = login_html()
assert 'action="' + base + '/login"' in body, "An untrusted header changed HTTP scheme"
logs = subprocess.run(dc + ["logs", "--no-log-prefix", "--tail", "100", "webserver"], capture_output=True, text=True, check=True).stdout
peers = re.findall(r"(?m)^(\d+\.\d+\.\d+\.\d+) - -", logs)
peer = next(address for address in reversed(peers) if address != "127.0.0.1")
ipaddress.ip_address(peer)
mapping = env_file.with_name("qa-proxy.map")
mapping.write_text(peer + " $http_x_forwarded_proto;\n")
override = env_file.with_name("qa-proxy.json")
override.write_text(json.dumps({"services": {"webserver": {"volumes": [{"type": "bind", "source": mapping.as_posix(), "target": "/etc/nginx/trusted-proto.map", "read_only": True}]}}}))
try:
    subprocess.run(dc + ["-f", str(override), "up", "-d", "--no-build", "--no-deps", "--wait", "--wait-timeout", "90", "webserver"], check=True)
    body = login_html()
    assert 'action="https://127.0.0.1:' + values["WEB_PORT"] + '/login"' in body
    assert "evil.invalid" not in body
    print("PASS default rejects spoofed scheme; explicit QA peer propagates HTTPS; forwarded host ignored", flush=True)
finally:
    subprocess.run(dc + ["up", "-d", "--no-build", "--no-deps", "--wait", "--wait-timeout", "90", "webserver"], check=True)
assert 'action="' + base + '/login"' in login_html()
print("PASS restored empty production peer whitelist; no production proxy map edited", flush=True)
