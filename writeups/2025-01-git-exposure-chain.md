# Case Study — Exposed Git Repository and the Breach Chain It Enabled

> **Sanitized writeup.** The client is described generically and all target
> addresses, credentials, and secrets have been changed or removed. Techniques
> reproduce in the [lab](../lab/). See [DISCLAIMER](../DISCLAIMER.md).

## Context

A regional-government web application used to manage fixed-asset / regional
property records. The engagement was a **black-box, external** assessment of a
single production host, with a short testing window. No credentials were provided.

## Objective

Determine whether an unauthenticated internet attacker could obtain access to
sensitive data or impact the integrity of the asset ledger.

## What was found

The very first enumeration step paid off: the production web root exposed its
`.git` directory.

```
$ curl -sS https://<target>/.git/HEAD
ref: refs/heads/main
```

Using `git-dumper`, the full source tree was reconstructed. Because
the repository contained committed secrets, source disclosure became a
credential and key material disclosure:

- a hard-coded symmetric key/IV used to "encrypt" URL parameters
  (`app/helpers/security.ini`);
- hard-coded credentials for an **internal** file-conversion service
  (`app/helpers/ftp_helper.php`);
- a complete map of endpoints, including several controllers with **no
  authentication check at all**.

## Why it mattered

Source disclosure is frequently dismissed as "just" information disclosure. Here
it was the entry point to everything else:

1. The leaked URL key let me forge "encrypted" identifiers, defeating the only
   access control on several record endpoints.
2. The leaked internal-service credentials provided a plausible pivot from the
   web tier into the internal network.
3. The endpoint map pointed directly at an unauthenticated SQL injection, which
   led to full database and credential disclosure (separate writeup).

## Root cause

- `.git` was deployed as part of the release artifact instead of being removed.
- Secrets were committed to version control instead of being injected at runtime.
- A `.htaccess` rule attempted to block `/.git`, but the deployment did not rely
  on a hardened web-server configuration.

## Remediation delivered

- Remove `.git` from deployed artifacts; build releases in CI rather than
  deploying a working clone.
- Deny dotfiles at the web-server layer (Apache `DirectoryMatch`, nginx
  `location ~ /\.`), not only via `.htaccess`.
- Rotate every secret that ever lived in the repository.
- Add secret scanning (e.g. gitleaks/trufflehog) as a CI gate.

## Lessons / takeaways

- Check `/.git/`, `/.svn/`, `/.hg/`, backups, and IDE files on every external
  assessment — it is cheap and occasionally decisive.
- "Information disclosure" should be rated by what it unlocks, not in isolation.
- A single leaked credential turns a web finding into a network finding.

## CVSS

`CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N` — **7.5 High** (escalates to
Critical when chained with the SQL injection finding).
