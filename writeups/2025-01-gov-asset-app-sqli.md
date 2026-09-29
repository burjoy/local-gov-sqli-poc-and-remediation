# Case Study — Pre-Auth SQL Injection to Full Credential Disclosure

> **Sanitized writeup.** Client, host, and all account data are anonymized.
> The full request/response for the real target is not published. A runnable
> reproduction of the same bug class lives in the [lab](../lab/). See
> [DISCLAIMER](../DISCLAIMER.md).

## Context

Same engagement as the [Git-exposure case study](2025-01-git-exposure-chain.md):
an external, black-box test of a government asset-management web app. After
recovering the source from an exposed `.git`, I had a complete list of endpoints
— including one controller whose authentication check had been commented out.

## The vulnerable code

The endpoint built SQL by string concatenation and interpolated a request
parameter straight into a `LIKE` clause:

```php
// app/modules/bmd/controllers/Common.php  (anonymized, line ~104)
$tahun = $this->input->get('tahun');
...
$query->where("A.tgl_perolehan LIKE '$tahun%'");
```

Because the value is placed inside single quotes and never escaped, a quote
closes the literal and the rest of the query is attacker-controlled. The
controller's constructor guard was disabled, so the endpoint required no login.

## Detection

Supplying a single quote produced a database error that reflected the whole
query — including the injected string — back to the browser. That is a textbook
error-based confirmation. (The same response also leaked the absolute deploy
path and the framework's debug mode, which was left enabled.)

## Exploitation

The `SELECT` had nine columns, so a `UNION SELECT` with nine values returned
attacker-chosen rows inside the HTML:

```
GET /bmd/common/view_bulk_sticker?kib=kib_a&upb=1.1.1.1.1
    &tahun=2024%27%20UNION%20SELECT%20username,2,3,password,5,6,7,8,9
           %20FROM%20user_login--%20-
```

The rendered page contained an account username and its password hash. Because
only some columns are printed by the template, the payload places the data in
those positions. No blind technique, no out-of-band channel, and no credentials
were required.

Password storage was **bcrypt** (`$2a$12$…`, per-hash salt), so offline cracking
was slow and unnecessary — the injection already provided full database read
(and write) access. Impact does not depend on cracking the hash.

## Impact

- Full confidentiality loss: the entire database, including `user_login`, is
  readable by an anonymous attacker.
- Integrity/availability: UNION and stacked techniques allow modification or
  deletion of ledger records (`is_approved`, `is_deleted`, asset rows).
- Credential exposure: password hashes are disclosed and subject to offline
  attack and password reuse.

## CVSS

`CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H` — **9.8 Critical**

- CWE-89 SQL Injection; CWE-522 Insufficiently Protected Credentials
- OWASP A03:2021 Injection

## Remediation delivered

```php
// Parameterize — let the DB driver bind the value
$query->where('A.tgl_perolehan LIKE', $tahun . '%');
// and validate $tahun as 4 digits
```

- Restore the authentication guard on the controller.
- Audit the whole codebase for the same raw-interpolation pattern
  (`where("... '$var' ...")`, `having("... '" . $var . "' ...")`) — the same bug
  was present in the KIB models, several report controllers, and the shared
  search helpers.
- Write password hashing consistently with `password_hash()`/`password_verify()`.
- Disable debug/error display in production.

## Reproduce locally

The [lab](../lab/) rebuilds this exact flow (vulnerable PHP app + SQLite seed +
PoC script). It is safe to run and share because it never touches the client.

```bash
cd lab && docker compose up --build
./exploits/sqli_union.sh http://localhost:8080
```

## Lessons / takeaways

- A commented-out auth check plus one unescaped parameter equals full compromise.
- Error-based reflection and verbose debug output massively accelerate exploitation.
- Rate findings by actual data exposure: bcrypt did not reduce this to Medium,
  because read/write database access was already achieved.
