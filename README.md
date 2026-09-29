# SQLi PoC and Lab

> **Authorized assessments only.** All client work shown here is anonymized and
> redacted. The `lab/` application is **intentionally vulnerable** and must only
> ever be run locally. See [DISCLAIMER.md](DISCLAIMER.md).

Offensive-security PoC and lab: web application penetration testing, vulnerability
research, root-cause analysis, and professional reporting.

## Skills demonstrated

| Area | Techniques | Evidence |
|---|---|---|
| SQL injection | Pre-auth UNION-based, error-based, table-name injection | [writeup](writeups/2025-01-gov-asset-app-sqli.md), [lab](lab/) |
| Source / secret disclosure | Exposed `.git`, secret recovery, credential pivot | [writeup](writeups/2025-01-git-exposure-chain.md) |
| Recon & enumeration | Directory brute force, endpoint mapping | [methodology/recon.md](methodology/recon.md) |
| Reporting | CVSS v3.1, CWE/OWASP mapping, remediation | [reports/](reports/) |

## Contents

- **[writeups/](writeups/)** — sanitized case studies of real engagements.
  - [Uncovering an exposed Git repository and the breach chain it enabled](writeups/2025-01-git-exposure-chain.md)
  - [Pre-auth SQL injection to full credential disclosure in a government asset portal](writeups/2025-01-gov-asset-app-sqli.md)
- **[reports/](reports/)** — report template and a redacted sample report.
  - [report-template.md](reports/report-template.md)
  - [sample-report-redacted.md](reports/sample-report-redacted.md)
- **[methodology/](methodology/)** — how I approach recon, web testing, and reporting.
- **[lab/](lab/)** — a self-contained, intentionally vulnerable PHP app reproducing
  the same bug class, with a runnable PoC. **Local use only.**
- **[tools/](tools/)** — small helpers and notes.

## Run the lab

```bash
cd lab
docker compose up --build
# then, in another shell:
./exploits/sqli_union.sh http://localhost:8080
```

No Docker? `cd lab/vulnerable-app && php -S 0.0.0.0:8080 index.php` (requires `pdo_sqlite`).

Full details: [lab/README.md](lab/README.md).

## Disclaimer

Everything in this repository is for **education and authorized security testing**.
The lab contains deliberate vulnerabilities; do not deploy it to the internet.
No live client target, credential, or working exploit against a production system
is published here. See [DISCLAIMER.md](DISCLAIMER.md).

## License

Code: MIT. Documentation/writeups: CC BY 4.0. See [LICENSE](LICENSE).
