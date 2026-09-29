# Tools

Small helpers and notes. Nothing here targets a live system.

| Tool | Purpose |
|---|---|
| `sqli_union.sh` (in [../lab/exploits/](../lab/exploits/)) | Lab-only UNION SQLi PoC against the local vulnerable app |

Planned / ideas:

- `check_git_exposure.sh` — non-intrusively test whether `/.git/HEAD` is
  reachable on a host **you are authorized to test**, and print the result.
- `redact.py` — strip usernames, hashes, IPs, and URLs from evidence before it
  goes into a report.

Third-party tools I rely on: Burp Suite, sqlmap, ffuf/dirsearch, nuclei,
git-dumper, nmap, gitleaks.
