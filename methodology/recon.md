# Methodology — Reconnaissance

My recon is deliberately repeatable. The goal is to create an accurate attack
surface map before touching anything intrusive.

## 1. Scope confirmation
- Confirm in-scope hosts/IPs/cidrs and the exact testing window.
- Record tester source IPs so client logs are attributable.

## 2. Passive / OSINT
- Search engines and certificate transparency for hostnames.
- Public code (GitHub, `.git` mirrors) for leaked endpoints or secrets.
- Shodan/Censys/FOFA for exposed services and banners.

## 3. Active enumeration
- DNS resolution and virtual-host discovery.
- Port scan of in-scope hosts; HTTP(S) service ID and tech fingerprinting.
- Content discovery (directory/file brute force) with technology-appropriate
  wordlists, then a second pass seeded with names found in JS/source.

## 4. Web surface mapping
- Crawl with an authenticated and an unauthenticated session where possible.
- Enumerate parameters, API routes, and unusual verbs.
- **Always check version-control and backup artifacts**: `/.git/`, `/.svn/`,
  `/.hg/`, `/.env`, `*.bak`, `*.zip`, `/*.sql`, editor/IDE folders.
- Note verbose error pages, stack traces, and debug endpoints — they shorten
  every later step.

## 5. Output
A map: hosts, ports, app paths, parameters, auth state per endpoint,
and a shortlist of likely vulnerability classes. This list drives the testing
phase and keeps effort focused.

## Automation vs. manual
Automation finds breadth (exposed files, default pages, known CVEs). Manual
testing finds the impactful logic bugs — broken access control and injection.
I use both, and I verify every automated hit by hand before reporting it.
