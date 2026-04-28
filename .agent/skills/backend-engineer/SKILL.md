---
name: backend-engineer
description: Build robust backend systems with PHP/Laravel (TALL Stack) as primary. Supports Node.js/React/Vue as secondary. Use when designing APIs, implementing authentication, optimizing database queries, or developing production-ready backend systems.
---

# Backend Engineer (Optimized)

**Primary Stack:** PHP / Laravel / Livewire / MySQL (TALL Stack).
**Secondary Stack:** Node.js / React / Vue (via Inertia.js).

## Core Directives

- **Laravel First:** Use Eloquent, Service/Action patterns, and Laravel built-in security features.
- **API Standards:** REST (default), API Resources for transformation, Scribe for docs.
- **Database:** MySQL (default). Use Migrations, Seeders, and Factories. Optimize with Eager Loading.
- **Scaling:** Laravel Queues for background jobs, Redis for cache/sessions.
- **Monitoring:** Pulse, Telescope, and Sentry for observability.

## Performance & Scaling Checklist

- [ ] Eloquent Eager Loading (prevent N+1)
- [ ] Route/Config/View Caching in production
- [ ] Redis for cache/sessions/queues
- [ ] Query optimization (indexes, EXPLAIN)
- [ ] Heavy tasks moved to Background Jobs (Queues)

## Verification Checklist

- [ ] All inputs validated (Form Requests)
- [ ] Authorization checked (Policies/Gates)
- [ ] Business logic moved from Controllers to Services/Actions
- [ ] API responses use Resources
- [ ] `php artisan test` is green

## Patterns & Examples

For detailed code patterns (Auth, API Design, Architecture, Performance), refer to:
- `references/*.md` (All 9 reference files)
- `references/architecture.md` - Service/Action patterns
- `references/api-design.md` - Laravel REST patterns