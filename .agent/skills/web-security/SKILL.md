---
name: web-security
description: Comprehensive security practices for Laravel web applications and SaaS platforms. Covers OWASP, multi-tenant isolation, LGPD, and AI-generated code vulnerability prevention. Activate proactively on EVERY feature.
---

# Web Security (Optimized)

**Goal:** Security by default. Treat every line of code as an attack surface.

## Core Directives

- **AI Code Vulnerability Prevention:** Never trust AI-generated code. Review for mass assignment, missing authorization, and insecure defaults.
- **Input & Output:** Validate everything (Form Requests), escape everything (Blade `{{ }}`), never trust `$request->all()`.
- **Authorization:** Every endpoint and action MUST be verified by a Policy or Gate.
- **Data Privacy (LGPD):** Encrypt PII at rest (`encrypted` cast), never log PII, mask PII in UI.
- **Multi-Tenant (SaaS):** Absolute data isolation. Use Global Scopes and verify every query.
- **File Security:** Validate MIME type/extension, use private disks (S3), never use user-supplied filenames.

## Security Gates (Check BEFORE completion)

- [ ] Input validated via Form Request (not `$request->all()`)
- [ ] Authorization via Policy (not just `auth` middleware)
- [ ] Eager Loading used to prevent N+1 data exposure
- [ ] Sensitive data not exposed in API response/logs
- [ ] CSRF protection active on forms
- [ ] No hardcoded secrets or debug code
- [ ] Multi-tenant isolation verified

## Detailed References

Refer to these for specific "How-To" and Code Patterns:
- `references/ai-code-security.md` - AI-generated code vulnerabilities
- `references/web-security.md` - OWASP Top 10 for Laravel
- `references/saas-security.md` - Multi-tenant isolation patterns
- `references/data-privacy.md` - LGPD & PII handling
- `references/security-testing.md` - Security-focused tests (Pest)
- `references/infrastructure-security.md` - Server hardening
- `references/security-checklist.md` - Master audit list
