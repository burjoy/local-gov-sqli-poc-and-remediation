> **CLASSIFICATION: CONFIDENTIAL — {{DISTRIBUTION_LIST}}**
> Fill every `{{PLACEHOLDER}}`. Delete instructional notes (lines starting with `NOTE:`) before delivery.

---

# {{CLIENT_NAME}} — Penetration Test Report

## {{SYSTEM_NAME}} (`{{TARGET_URL}}`)

| | |
|---|---|
| **Report title** | Web Application Penetration Test — {{SYSTEM_NAME}} |
| **Client** | {{CLIENT_NAME}} |
| **Target / asset** | {{TARGET_URL}} ({{TARGET_IP_OR_RANGE}}) |
| **Assessment type** | {{BLACK_BOX / GREY_BOX / WHITE_BOX}} |
| **Engagement dates** | {{START_DATE}} – {{END_DATE}} |
| **Report version** | {{v1.0}} |
| **Report date** | {{REPORT_DATE}} |
| **Prepared by** | {{TESTER_NAME}}, {{ORG_NAME}} |
| **Reviewed by** | {{REVIEWER_NAME}} |
| **Classification** | Confidential |
| **Status** | {{Draft / Final}} |

### Version history

| Version | Date | Author | Changes |
|---|---|---|---|
| 0.1 | {{DATE}} | {{AUTHOR}} | Initial draft |
| 1.0 | {{DATE}} | {{AUTHOR}} | Final report after QA |

---

## 1. Executive summary

`NOTE: 1 page max, non-technical. No exploit payloads. Focus on business risk.`

{{ORG_NAME}} was engaged by {{CLIENT_NAME}} to assess the security of **{{SYSTEM_NAME}}** (`{{TARGET_URL}}`) between {{START_DATE}} and {{END_DATE}}. Testing was performed as a {{BLACK/GREY/WHITE}}-box assessment against the production system.

The assessment identified **{{N}}** findings: **{{C}} Critical**, **{{H}} High**, **{{M}} Medium**, **{{L}} Low**, and **{{I}} Informational**.

Most critically, the external-facing application can be fully compromised by an **unauthenticated** attacker. Specifically:

- The production web server exposes its **version-control repository (`.git`)**, allowing anyone to download the full application source code and secrets committed to it.
- A **pre-authentication SQL injection** allows an attacker to read the entire database, including the credential store (`user_login`), and to modify or delete asset records.

Chain these together and an anonymous internet user can reconstruct the source, recover hard-coded credentials and cryptographic keys, and exfiltrate account password hashes — without ever logging in. The business impact is loss of confidentiality of the asset ledger and user accounts, potential unauthorized modification/deletion of government asset data, and a likely pivot into internal services using exposed credentials.

**Headline recommendations:**

1. Remove the exposed `.git` directory and rotate **all** secrets that were committed to the repository, immediately.
2. Fix the unauthenticated SQL injection and restore the missing authentication guard on the affected controller.
3. Disable debug/error display in production and perform a full review of the remaining pre-auth endpoints listed in this report.

`NOTE: Add one sentence on regulatory/legal obligations (e.g. regional data-protection/PPID, data-integrity duties) if applicable.`

---

## 2. Scope

### 2.1 In scope

| # | Asset | Description |
|---|---|---|
| 1 | `{{TARGET_URL}}` | Production web application |
| 2 | {{TARGET_IP_OR_RANGE}} | {{HOSTING_DETAIL}} |
| 3 | {{API_OR_ENDPOINT_RANGE}} | {{DESCRIPTION}} |

### 2.2 Out of scope

- {{OUT_OF_SCOPE_1}}
- Denial-of-service / stress testing (not performed unless agreed)
- Social engineering
- Third-party hosted services not owned by {{CLIENT_NAME}}

### 2.3 Rules of engagement

| Item | Detail |
|---|---|
| Testing window | {{WINDOW}} ({{TIMEZONE}}) |
| Source IP(s) used | {{TESTER_IPS}} |
| Testing type | {{BLACK/GREY/WHITE}}-box |
| Safe words / stop conditions | {{CONTACT_AND_PROCEDURE}} |
| Emergency contact | {{CLIENT_CONTACT}} / {{TESTER_CONTACT}} |

### 2.4 Limitations & assumptions

- Testing was time-boxed to {{DURATION}}; coverage of every endpoint is not guaranteed.
- No destructive payloads were executed against live data (`NOTE: adjust if true/false. If `migrate_kdp` or write SQLi was tested, state whether data was affected and how it was restored.`).
- Findings reflect the state of the system at the time of testing.

---

## 3. Methodology & risk rating

### 3.1 Standards followed

- {{PTES}} / {{OWASP WSTG}} / {{NIST SP 800-115}}
- CVSS v3.1 for severity scoring

### 3.2 Tools used

| Tool | Version | Purpose |
|---|---|---|
| {{TOOL}} | {{VERSION}} | {{PURPOSE}} |

`NOTE: Common entries: Burp Suite, sqlmap, ffuf/dirsearch, git-dumper, nuclei, nmap. Include exact versions.`

### 3.3 Severity bands

| Rating | CVSS score | Meaning |
|---|---|---|
| Critical | 9.0 – 10.0 | Immediate, severe business impact; exploit likely |
| High | 7.0 – 8.9 | Serious impact; exploit feasible |
| Medium | 4.0 – 6.9 | Moderate impact; requires effort/conditions |
| Low | 0.1 – 3.9 | Minor impact |
| Informational | 0.0 | Best-practice / observation |

### 3.4 Risk matrix

| Likelihood \ Impact | Low | Medium | High |
|---|---|---|---|
| **High** | Medium | High | Critical |
| **Medium** | Low | Medium | High |
| **Low** | Low | Low | Medium |

---

## 4. Summary of findings

**Totals:** {{C}} Critical · {{H}} High · {{M}} Medium · {{L}} Low · {{I}} Informational

| ID | Finding | Severity | CVSS | Affected asset | Status |
|---|---|---|---|---|---|
| VULN-01 | Exposed Git repository enables source code & secret disclosure | High | 7.5 | `/.git/` | Open |
| VULN-02 | Unauthenticated SQL injection in `view_bulk_sticker` (full DB & credential disclosure) | Critical | 9.8 | `/bmd/common/view_bulk_sticker` | Open |
| VULN-03 | {{TITLE}} | {{SEV}} | {{CVSS}} | {{ASSET}} | Open |
| VULN-04 | {{TITLE}} | {{SEV}} | {{CVSS}} | {{ASSET}} | Open |

`NOTE: Add rows for the remaining findings (see assessment inventory): unauthenticated Common controller (mass_approve/migrate_kdp), unauthenticated parameter-module CRUD, unauth SQLi in search endpoints, Preview SQLi/LFI, reflected XSS, debug mode, hardcoded FTP credentials, base64 "encryption", CSRF, password hashing inconsistency, mass assignment, broken authorization.`

---

## 5. Attack narrative

`NOTE: Tell the story of the chain. This is the most persuasive section for management.`

An unauthenticated attacker can compromise the application end-to-end as follows:

1. **Reconnaissance.** The attacker identifies `{{TARGET_URL}}` via `{{DISCOVERY_METHOD}}`.
2. **Source disclosure (VULN-01).** A request to `{{TARGET_URL}}/.git/HEAD` returns `200 OK`; the attacker reconstructs the full source tree with `git-dumper`. From the source, the attacker recovers:
   - the URL-encryption key/IV in `app/helpers/security.ini`;
   - internal file-service credentials in `app/helpers/ftp_helper.php`;
   - a full map of unauthenticated endpoints.
3. **Database compromise (VULN-02).** With no credentials, the attacker sends a crafted `tahun` value to `{{TARGET_URL}}/bmd/common/view_bulk_sticker` and performs a `UNION SELECT` against `user_login`, receiving usernames and bcrypt password hashes in the HTML response.
4. **Impact.** The attacker now holds the asset ledger data and account password hashes, can modify/delete records through the same injection, and can attempt to use the leaked service credentials to pivot internally.

`NOTE: Insert a simple flow diagram if desired: Internet → .git dump → secrets → unauth SQLi → DB/credentials.`

---

## 6. Detailed findings

`NOTE: Duplicate the block below for every finding. Do not merge distinct root causes.`

---

### VULN-01 — Exposed Git repository leads to source code and secret disclosure

| Field | Value |
|---|---|
| **Severity** | High |
| **CVSS v3.1** | `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N` (7.5) |
| **Affected asset** | `{{TARGET_URL}}/.git/` (`.git/HEAD`, `.git/config`) |
| **CWE** | CWE-527 (Exposure of Version Control Repository), CWE-538 |
| **OWASP** | A05:2021 – Security Misconfiguration |
| **Status** | Open |

**Description.**
The production web root exposes the application's `.git` directory. An unauthenticated attacker can download the repository using standard tooling, reconstructing the complete application source. Sensitive material committed to the repository is therefore exposed, including a URL-encryption key/IV (`app/helpers/security.ini`) and hard-coded credentials for an internal file service (`app/helpers/ftp_helper.php`). The source also reveals all unauthenticated endpoints and injection points.

**Steps to reproduce.**
1. `curl -sS {{TARGET_URL}}/.git/HEAD` → HTTP 200, `ref: refs/heads/...`.
2. `python3 git-dumper.py {{TARGET_URL}}/.git/ ./dump` → full source recovered.
3. Inspect `app/helpers/security.ini` and `app/helpers/ftp_helper.php`.

**Evidence.**
- Appendix A: raw HTTP response for `/.git/HEAD`.
- Appendix B: directory listing / file count of recovered repository.
- Appendix C: redacted secret values recovered from source.

**Impact.**
Full application source, cryptographic key material, and internal-service credentials are exposed to any network attacker. This substantially lowers the effort required for every other finding in this report and provides a potential pivot from the web tier into the internal network.

**Remediation.**
- Remove `.git` from deployed artifacts; deploy from a build pipeline rather than a clone.
- Deny dotfiles at the web-server layer (Apache: `<DirectoryMatch "/\."> Require all denied`; nginx: `location ~ /\. { deny all; }`), not only via `.htaccess`.
- **Rotate every secret** present in the repository (FTP credentials, `security.ini` key/IV, DB credentials, API tokens).
- Add automated secret-scanning to CI.

**References.** CWE-527; OWASP WSTG-CONF-04.

---

### VULN-02 — Unauthenticated SQL injection in `view_bulk_sticker` leads to full database and credential disclosure

| Field | Value |
|---|---|
| **Severity** | Critical |
| **CVSS v3.1** | `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H` (9.8) |
| **Affected asset** | `GET {{TARGET_URL}}/bmd/common/view_bulk_sticker` (parameter `tahun`) |
| **Related endpoints** | `POST /bmd/common/get_ref_*_by_search` (`search`), `POST /getJson` (`params`), `POST /home/get_upb_kib` (`kib`) |
| **CWE** | CWE-89 (SQL Injection), CWE-522 (Insufficiently Protected Credentials) |
| **OWASP** | A03:2021 – Injection |
| **Status** | Open |

**Description.**
The `tahun` parameter is interpolated directly into a SQL `LIKE` clause (`app/modules/bmd/controllers/Common.php:104` and `:133`) without parameterization. The controller's authentication check is commented out (`Common.php:16-19`), making the issue exploitable pre-authentication. A `UNION SELECT` payload returns arbitrary rows in the HTML response.

**Steps to reproduce.**
1. Request:
   ```
   GET /bmd/common/view_bulk_sticker?kib=kib_a&upb=1.1.1.1.1
       &tahun=2024%27%20UNION%20SELECT%20username,2,3,password,5,6,7,8,9%20FROM%20user_login--%20-
   ```
   against `{{TARGET_URL}}`.
2. The response HTML renders the attacker-supplied columns, including the `username` and `password` hash from `user_login`.

`NOTE: Keep the exact payload; redact real usernames/hashes in the body and store full output in the appendix.`

**Evidence.**
- Appendix D: full HTTP request/response with a redacted account row.
- Appendix E: MySQL error 1064 showing the injected string reflected inside the query (proof of inline construction).
- Appendix F: debug output leaking absolute path `/appserv/www/deploy/...` and controller path.

**Impact.**
An unauthenticated attacker can read the entire database — including the credential store — and, via UNION/stacked techniques, modify or delete asset records (`is_approved`, `is_deleted`). Account password hashes were disclosed in bcrypt format; offline cracking was not required to demonstrate impact, but the hashes are subject to offline attack and password reuse.

**Analyst note on credentials.**
Hashes are bcrypt `$2a$12$...` with per-user salt, which resists offline cracking; the finding remains Critical because direct database read/write is achieved regardless of hash strength. Force a password reset for all accounts and treat all disclosed hashes as compromised.

**Remediation.**
- Use bound parameters: `$query->where('A.tgl_perolehan LIKE', $tahun.'%');` and validate `$tahun` as a 4-digit value.
- Restore the authentication guard in `Common::__construct`.
- Remediate the same raw `where("... '$var' ...")` / `having("... '".$var."' ...")` pattern throughout `Kib_*_model.php`, the `laporan/*` controllers, and `Common_model.php`.
- Disable `display_errors` and set `CI_ENV=production`.

**References.** CWE-89; OWASP SQL Injection Prevention Cheat Sheet; WSTG-INPV-05.

---

### VULN-03 — {{FINDING_TITLE}}  *(blank template — duplicate as needed)*

| Field | Value |
|---|---|
| **Severity** | {{Critical/High/Medium/Low/Info}} |
| **CVSS v3.1** | `{{CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H}}` ({{SCORE}}) |
| **Affected asset** | {{ASSET / ENDPOINT / PARAMETER}} |
| **CWE** | {{CWE-ID}} ({{NAME}}) |
| **OWASP** | {{CATEGORY}} |
| **Status** | Open |

**Description.**
{{WHAT_IT_IS_AND_WHY_IT_EXISTS}}

**Steps to reproduce.**
1. {{STEP_1}}
2. {{STEP_2}}

**Evidence.**
- Appendix {{X}}: {{REQUEST_RESPONSE_SCREENSHOT_TOOL_OUTPUT}}

**Impact.**
{{TECHNICAL_IMPACT — C/I/A}}
{{BUSINESS_IMPACT — data, finance, reputation, compliance}}

**Remediation.**
- {{SPECIFIC_FIX_1}}
- {{DEFENSE_IN_DEPTH}}

**References.** {{CWE_LINK / OWASP / VENDOR_ADVISORY}}

---

## 7. Remediation roadmap

`NOTE: Prioritize by risk and effort. Give owners and target dates.`

| Priority | Finding(s) | Action | Owner | Effort | Target date |
|---|---|---|---|---|---|
| P1 | VULN-02 | Fix SQLi with parameterized queries; restore `Common` auth guard | {{TEAM}} | {{Low}} | {{DATE}} |
| P1 | VULN-01 | Remove `.git`; rotate all exposed secrets | {{TEAM}} | {{Low}} | {{DATE}} |
| P1 | VULN-01/02 | Set `CI_ENV=production`, `display_errors=0` | {{TEAM}} | {{Low}} | {{DATE}} |
| P2 | {{VULN}} | {{ACTION}} | {{TEAM}} | {{MED}} | {{DATE}} |
| P3 | {{VULN}} | {{ACTION}} | {{TEAM}} | {{HIGH}} | {{DATE}} |

### Short-term mitigations (if full fixes are delayed)

- Block access to `/.git` and dotfiles at the WAF/web server.
- Add WAF rules for the affected endpoints.
- Rotate leaked credentials immediately.

### Retest criteria

| Finding | Pass condition |
|---|---|
| VULN-01 | `/.git/HEAD` and `/.git/config` return 403/404; all committed secrets rotated |
| VULN-02 | `tahun` injection returns generic error/no injected data while unauthenticated; parameterized query in code review |
| {{VULN}} | {{PASS_CONDITION}} |

---

## 8. Appendix index

| Appendix | Contents | File / Location |
|---|---|---|
| A | `/.git/HEAD` raw response | {{LINK}} |
| B | Recovered repository listing / file count | {{LINK}} |
| C | Redacted recovered secrets | {{LINK}} |
| D | SQLi full request/response (redacted) | {{LINK}} |
| E | MySQL 1064 error with injected string | {{LINK}} |
| F | Debug/backtrace leak (paths, query) | {{LINK}} |
| G | Tool output & scan logs | {{LINK}} |
| H | Timeline of testing activity | {{LINK}} |
| I | Evidence integrity hashes (SHA-256) | {{LINK}} |
| J | Testing team & contacts | {{LINK}} |

---

## 9. Evidence handling

- Full artifacts stored in {{EVIDENCE_REPO}} with restricted access.
- Each evidence file accompanied by a SHA-256 hash and capture timestamp.
- Sensitive values (usernames, hashes, secrets) redacted in the report body; full values only in the controlled appendix.
- Evidence retained for {{RETENTION_PERIOD}} and destroyed thereafter.

---

*End of report — {{REPORT_VERSION}} — Confidential*
