# Methodology — Reporting

A finding is not "done" until it is written so a developer can fix it and a
manager can prioritize it without a call.

## Structure I use
See [../reports/report-template.md](../reports/report-template.md) for the full
skeleton. Sections: document control, executive summary, scope/RoE, methodology
and risk rating, findings summary, attack narrative, detailed findings,
remediation roadmap, appendices, evidence handling.

## Every finding includes
- ID, title, severity, CVSS v3.1 vector and score
- Affected asset (host/endpoint/parameter/version)
- CWE and OWASP category
- Description (what + why)
- Steps to reproduce (exact, repeatable)
- Evidence (redacted request/response, screenshots, tool output)
- Impact (technical C/I/A + business/regulatory)
- Remediation (specific and actionable)
- References

## Rules
- **One root cause per finding.** Do not merge unrelated bugs, even if one
  request triggers both.
- **Reproduce exactly.** A developer must be able to recreate it from the steps
  alone, without asking me.
- **Rate by real impact**, not by bug class. A read-only SQLi that exposes
  credentials is Critical; a bcrypt hash does not lower it.
- **Redact in the body, preserve in a controlled appendix.** Partial usernames,
  truncated hashes in-line; full artifacts stored securely with SHA-256.
- **Show the chain.** An attack narrative (recon → entry → escalation → impact)
  is what demonstrates business risk to non-technical readers.
- **Define retest criteria.** Every finding ends with a concrete pass condition.
- **No exploit payloads in the executive summary.**

## Severity bands
Critical 9.0–10.0 · High 7.0–8.9 · Medium 4.0–6.9 · Low 0.1–3.9 · Info 0.0
(CVSS v3.1.)
