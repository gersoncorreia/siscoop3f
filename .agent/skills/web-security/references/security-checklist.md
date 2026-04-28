# Security Audit Checklist

## 1. Authentication & Authorization
- [ ] Passwords hashed (bcrypt/Argon2id)
- [ ] Throttling active (5 attempts/min)
- [ ] Session regeneration on login
- [ ] Policies for ALL CRUD operations
- [ ] 2FA available for Admin/Financial roles

## 2. Input & Data
- [ ] Form Requests used for ALL endpoints
- [ ] PII encrypted at rest (`encrypted` cast)
- [ ] PII masked in UI and hidden in logs
- [ ] Blade `{{ }}` used (no `{!! !!}` for user input)
- [ ] Files: Private disk, validated MIME/ext, random names

## 3. SaaS & Multi-Tenant
- [ ] Global scope enforced on all queries
- [ ] Auth context re-applied in Queue jobs
- [ ] Impersonation actions logged

## 4. LGPD / Privacy
- [ ] Consent recording active
- [ ] Data Export (JSON) available
- [ ] Data Erasure/Deletion available
- [ ] Data retention cleanup scheduled

## 5. Infrastructure & CI
- [ ] HTTPS enforced (HSTS active)
- [ ] Security headers (X-Frame, CSP)
- [ ] `.env` NOT web-accessible
- [ ] `composer audit` clean
- [ ] Production: `APP_DEBUG=false`, `--no-dev`
- [ ] Backups: Encrypted and remote
