"""Backup/recreate/restore drill: local simarta-smoke-* projects only, dummy data.

Usage: python tests/Deployment/production_persistence.py ABSOLUTE_ENV_FILE
Leaves QA volumes intact, stops QA containers; never deletes volumes.
"""
import json
import socket
import subprocess
import sys
from pathlib import Path

root = Path(__file__).resolve().parents[2]
source_env = Path(sys.argv[1]).resolve()
values = dict(line.split("=", 1) for line in source_env.read_text().splitlines() if "=" in line and not line.startswith("#"))
project = values["COMPOSE_PROJECT_NAME"]
assert project.startswith("simarta-smoke-") and values["DB_DATABASE"] == "simarta_smoke"
assert values["WEB_BIND_IP"] == "127.0.0.1" and values["WEB_PORT"] not in ("8001", "8500")
record = json.loads(source_env.with_name("smoke-record.json").read_text())


def command(env, name):
    assert name.startswith("simarta-smoke-")
    return ["docker", "compose", "--project-directory", str(root), "--env-file", str(env), "-f", str(root / "compose.production.yml"), "-p", name]


dc = command(source_env, project)


def run(args, **kwargs):
    return subprocess.run(args, check=True, **kwargs)


def checksum(dc):
    code = "<?php require 'vendor/autoload.php'; $a=require 'bootstrap/app.php'; $a->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); if(config('database.connections.mysql.database')!=='simarta_smoke')exit(90); $d=App\\Models\\LegalGovernanceDocument::findOrFail(" + str(int(record["id"])) + "); echo hash_file('sha256', storage_path('app/'.$d->file_path));"
    result = run(dc + ["exec", "-T", "app", "php"], input=code.encode(), capture_output=True)
    assert result.stdout.decode().strip() == record["hash"], "DB/file consistency mismatch"


run(dc + ["up", "-d", "--no-build", "--force-recreate", "--wait", "--wait-timeout", "180", "app", "scheduler", "webserver"])
checksum(dc)
print("PASS document row and checksum survived container recreation", flush=True)
backup = source_env.parent / "dummy-backup"
backup.mkdir(exist_ok=True)
run(dc + ["stop", "webserver", "scheduler", "app"])
with (backup / "database.sql").open("wb") as output:
    run(dc + ["exec", "-T", "db", "sh", "-c", 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysqldump --single-transaction --no-tablespaces --set-gtid-purged=OFF -u"$MYSQL_USER" "$MYSQL_DATABASE"'], stdout=output)
with (backup / "storage.tar.gz").open("wb") as output:
    run(dc + ["run", "--rm", "--no-deps", "--entrypoint", "tar", "app", "-czf", "-", "-C", "/var/www/html/storage", "."], stdout=output)
assert (backup / "database.sql").stat().st_size > 0
print("PASS consistent backup of isolated dummy DB and storage", flush=True)

restored = values.copy()
restored_project = project + "-restore"
with socket.socket() as probe:
    probe.bind(("127.0.0.1", 18501))
restored.update(COMPOSE_PROJECT_NAME=restored_project, WEB_PORT="18501", APP_URL="http://127.0.0.1:18501")
restored_env = source_env.with_name("restore.env")
restored["APP_ENV_FILE"] = restored_env.as_posix()
restored_env.write_text("\n".join(k + "=" + v for k, v in restored.items()) + "\n")
rdc = command(restored_env, restored_project)
run(rdc + ["up", "-d", "--no-build", "--wait", "--wait-timeout", "180", "db"])
with (backup / "database.sql").open("rb") as source:
    run(rdc + ["exec", "-T", "db", "sh", "-c", 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysql -u"$MYSQL_USER" "$MYSQL_DATABASE"'], stdin=source)
with (backup / "storage.tar.gz").open("rb") as source:
    run(rdc + ["run", "--rm", "--no-deps", "--entrypoint", "tar", "app", "-xzf", "-", "-C", "/var/www/html/storage"], stdin=source)
run(rdc + ["up", "-d", "--no-build", "--wait", "--wait-timeout", "180", "app", "scheduler", "webserver"])
checksum(rdc)
print("PASS restored NEW project/volumes: matching document DB row and SHA256", flush=True)
run(dc + ["down"])
run(rdc + ["down"])
print("QA containers/networks removed; all QA volumes retained; development/office untouched", flush=True)
