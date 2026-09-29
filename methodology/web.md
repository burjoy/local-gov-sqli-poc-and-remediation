# Methodology — Web Application Testing

Structured around OWASP WSTG, ordered by expected impact.

## 1. Authentication
- Login flow, lockout, rate limiting, MFA, password reset.
- Session handling: cookie flags, fixation, regeneration on login/logout.
- Password storage review (hash algorithm, salt, consistency across code paths).

## 2. Authorization (highest value)
- For every object reference: can another user/tenant read or modify it (IDOR)?
- For every privileged action: is the check server-side and role-aware?
- Trust boundaries: values the client sends that the server should derive itself
  (tenant id, role, price, approval status).

## 3. Injection
- SQL: single quotes in every parameter; then error/boolean/UNION techniques.
- Command, LDAP, template, and header injection.
- NoSQL / ORM query manipulation.
- Prefer manual confirmation; use sqlmap to accelerate, not to think.

## 4. Source & secret exposure
- VCS directories, backups, `.env`, config files, IDE artifacts.
- Secrets in client-side JS and in server responses.

## 5. Client-side
- Reflected and stored XSS (with context awareness).
- CSRF on state-changing requests.
- Open redirects, clickjacking, CORS misconfigurations.

## 6. Business logic
- Workflow bypass, quantity/price manipulation, approval circumvention.
- File upload: type, extension, content, and storage location.

## 7. Configuration
- Debug/verbose errors, default credentials, unnecessary HTTP methods,
  missing security headers, TLS issues.

## Evidence discipline
For each candidate finding: save the raw request, the raw response, a timestamp,
and a minimal reproduction. Confirm exploitability twice — once manually, once
with a clean repeat — before writing it up.
