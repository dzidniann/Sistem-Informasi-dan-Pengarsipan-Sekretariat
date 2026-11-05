"""Prepare a local, reviewable Git history cleanup. This script never pushes."""

import argparse
import json
import subprocess
from datetime import datetime
from pathlib import Path

REPOSITORY = "https://github.com/dzidniann/Sistem-Informasi-dan-Pengarsipan-Sekretariat.git"
ROOT = Path(__file__).resolve().parent.parent


def git(repo, *arguments, data=None):
    return subprocess.check_output(["git", "-C", str(repo), *arguments], input=data)


def prepare():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", required=True, help="New bare repository directory inside .dist")
    parser.add_argument("--local-ando", help="Optional checkout containing a reviewed, unpushed security commit")
    args = parser.parse_args()
    output = Path(args.output).resolve()
    if not output.is_relative_to((ROOT / ".dist").resolve()) or output.exists():
        raise SystemExit("Output must be a new directory inside this project's .dist folder.")

    subprocess.run(["git", "clone", "--mirror", REPOSITORY, str(output)], check=True)
    remote_heads = dict(
        line.split(" ", 1)
        for line in git(output, "for-each-ref", "--format=%(refname) %(objectname)", "refs/heads").decode().splitlines()
    )
    pending_commit = None
    if args.local_ando:
        local = Path(args.local_ando).resolve()
        if not local.is_relative_to((ROOT / ".dist").resolve()):
            raise SystemExit("The local checkout must be inside the project's .dist folder.")
        remote = git(local, "remote", "get-url", "origin").decode().strip()
        if remote != REPOSITORY:
            raise SystemExit("Local checkout uses a different remote repository.")
        pending_commit = git(local, "rev-parse", "refs/heads/ando").decode().strip()
        git(output, "fetch", "--no-tags", str(local), "refs/heads/ando:refs/heads/ando")
    # A bundle containing the original commits is confidential and stays outside public/.
    stamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    backup = ROOT / "storage" / "app" / "private-backups" / ("git-history-" + stamp)
    backup.mkdir(mode=0o700, parents=True, exist_ok=False)
    bundle = backup / "before-cleanup.bundle"
    git(output, "bundle", "create", str(bundle), "--all")
    heads = dict(
        line.split(" ", 1)
        for line in git(output, "for-each-ref", "--format=%(refname) %(objectname)", "refs/heads").decode().splitlines()
    )
    mapping = {}
    trees = {}
    commits = git(output, "rev-list", "--reverse", "--topo-order", "--branches").decode().splitlines()
    for old in commits:
        raw = git(output, "cat-file", "commit", old)
        header, message = raw.split(b"\n\n", 1)
        lines = []
        skip_signature = False
        for line in header.split(b"\n"):
            if line.startswith(b" "):
                if not skip_signature:
                    lines.append(line)
                continue
            skip_signature = line.split(b" ", 1)[0] in [b"gpgsig", b"gpgsig-sha256", b"mergetag"]
            if skip_signature:
                continue
            if line.startswith(b"tree "):
                tree = line[5:].decode()
                if tree not in trees:
                    entries = git(output, "ls-tree", "-z", tree).split(b"\0")
                    clean = [entry for entry in entries if entry and entry.split(b"\t", 1)[1] != b".env.local.backup"]
                    trees[tree] = git(output, "mktree", "-z", data=b"\0".join(clean) + b"\0").strip()
                line = b"tree " + trees[tree]
            elif line.startswith(b"parent "):
                line = b"parent " + mapping[line[7:].decode()].encode()
            lines.append(line)
        rewritten = b"\n".join(lines) + b"\n\n" + message
        mapping[old] = git(output, "hash-object", "-t", "commit", "-w", "--stdin", data=rewritten).decode().strip()

    plan = []
    for ref, old in heads.items():
        new = mapping[old]
        git(output, "update-ref", ref, new, old)
        changed = git(output, "diff", "--name-only", old, new).decode().splitlines()
        if any(path != ".env.local.backup" for path in changed):
            raise RuntimeError("Unexpected source-code change in rewritten history.")
        plan.append({"ref": ref, "expected_remote_sha": remote_heads[ref], "source_sha": old, "clean_sha": new, "changed_files": changed})
    if git(output, "rev-list", "--branches", "--", ".env.local.backup").strip():
        raise RuntimeError("Backup configuration still exists in branch history.")
    git(output, "fsck", "--no-reflogs", "--connectivity-only")
    other_refs = git(output, "for-each-ref", "--format=%(refname)", "refs/tags", "refs/pull").decode().splitlines()
    report = {"repository": REPOSITORY, "local_candidate": str(output), "private_backup_bundle": str(bundle), "pending_local_commit": pending_commit, "branches": plan, "other_refs_requiring_owner_review": other_refs, "pushed": False}
    report_path = output.parent / (output.name + "-plan.json")
    report_path.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, indent=2))


if __name__ == "__main__":
    prepare()
