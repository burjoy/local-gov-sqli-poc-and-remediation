# Redacted Sample Report

> This is a **condensed, sanitized** example of the reports I deliver. It shows
> structure, severity rating, and writing style. The full version uses the
> [report template](report-template.md). Client and target identifiers are removed.

---

## Executive summary

An external penetration test of a regional-government asset-management web
application identified **2 findings: 1 Critical and 1 High**. The application can
be fully compromised by an **unauthenticated** internet attacker:

- The production web root exposes its `.git` repository, disclosing the full
  source code and secrets committed to it.
- An unauthenticated SQL injection allows reading the entire database —
  including the credential store — and modifying or deleting asset records.

Chained, an attacker reconstructs the source, recovers hard-coded credentials and
cryptographic keys, and exfiltrates password hashes without logging in.

Top recommendations: remove the exposed `.git` and rotate all committed secrets;
fix the SQL injection and restore the missing authentication guard; disable debug
output in production.

## Summary of findings

| ID | Finding | Severity | CVSS |
|---|---|---|---|
| VULN-01 | Exposed Git repository → source and secret disclosure | High | 7.5 |
| VULN-02 | Unauthenticated SQL injection → full DB/credential disclosure | Critical | 9.8 |

## Scope

| Asset | Description |
|---|---|
| `https://<redacted>` | Production web application (black-box, external) |

Testing window: `<redacted>`. Methodology: OWASP WSTG, CVSS v3.1. Tools: Burp
Suite, sqlmap, ffuf, git-dumper.

## Detailed findings

### VULN-01 — Exposed Git repository leads to source code and secret disclosure

- **Severity:** High — `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N` (7.5)
- **Affected asset:** `<target>/.git/`
- **CWE:** CWE-527, CWE-538 · **OWASP:** A05:2021

**Description.** The web root exposes `.git`. An unauthenticated attacker can
reconstruct the source with standard tooling, exposing a URL-encryption key/IV
and hard-coded internal file-service credentials committed to the repository.

**Steps to reproduce.** `curl <target>/.git/HEAD` returns `200 OK`, then
`git-dumper <target>/.git ./dump` recovers the tree.

**Evidence.** Appendix A (raw `/.git/HEAD` response); Appendix C (redacted
secrets).

**Impact.** Full source, keys, and internal-service credentials exposed; enables
every other finding and a potential internal pivot.

**Remediation.** Remove `.git` from deployment artifacts; deny dotfiles at the
web-server layer; rotate all exposed secrets; add CI secret scanning.

### VULN-02 — Unauthenticated SQL injection leads to full credential disclosure

- **Severity:** Critical — `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H` (9.8)
- **Affected asset:** `GET <target>/bmd/common/view_bulk_sticker` (parameter `tahun`)
- **CWE:** CWE-89, CWE-522 · **OWASP:** A03:2021

**Description.** The `tahun` parameter is interpolated into a SQL `LIKE` clause
without parameterization; the controller's auth check is disabled.

**Steps to reproduce.** A `UNION SELECT` payload on `tahun` returns rows from
`user_login` in the HTML response. (Payload redacted; see private engagement
records.)

**Evidence.** Appendix D (redacted request/response); Appendix E (DB error
reflecting the injected string).

**Impact.** Pre-auth read of the entire database, write/delete capability on
asset records, and disclosure of account password hashes (bcrypt).

**Remediation.** Use bound parameters; validate `tahun`; restore the auth guard;
audit for the same pattern codebase-wide; set `CI_ENV=production`.

## Attack narrative

`.git` exposed → source + secrets recovered → unauthenticated SQLi identified →
`user_login` dumped → hashes disclosed. One request each for steps 1 and 3, with
no credentials at any point.

## Remediation roadmap

| Priority | Finding | Action | Effort |
|---|---|---|---|
| P1 | VULN-02 | Parameterize queries; restore auth guard | Low |
| P1 | VULN-01 | Remove `.git`; rotate all secrets | Low |
| P1 | Both | Disable debug output in production | Low |

## Retest criteria

- `/.git/HEAD` and `/.git/config` return 403/404; all secrets rotated.
- Injection payload returns a generic error with no injected data while unauthenticated.

## Appendix index

A – `/.git/HEAD` response · B – repo listing · C – redacted secrets ·
D – SQLi request/response · E – DB error · F – tool logs · G – evidence hashes.
