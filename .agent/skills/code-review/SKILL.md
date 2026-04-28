---
name: code-review
description: Code review practices emphasizing technical rigor and verification. Use when receiving feedback, finishing tasks, or before completion claims.
---

# Code Review (LEAN)

**Goal:** Technical correctness over social comfort. Evidence before claims.

## Verification Gates (MANDATORY)

Before claiming success or committing code:
1. **Style:** `./vendor/bin/pint --test` (Must pass)
2. **Logic:** `php artisan test` (Must be Green)
3. **Drafting:** `./vendor/bin/phpstan analyse` (If configured)

## Decision Highlights

- STOP and clarify unclear feedback.
- Verify technically before implementing external suggestions.
- Request systematic review after EVERY major sub-task.

## Laravel Review Gates

- [ ] Form Requests used for validation
- [ ] Policies used for authorization
- [ ] Eloquent relationships correctly defined
- [ ] No N+1 queries (Eager loading verified)
- [ ] Migrations are reversible (`down` method exists)

## Detailed Protocols & Checklists

- `references/verification-gates.md` - Evidence protocol
- `references/requesting-review.md` - Review processes
- `references/receiving-feedback.md` - Feedback handling