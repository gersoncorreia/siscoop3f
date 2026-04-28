---
name: software-engineer
description: Core software engineering principles for code style, documentation, and development workflow.
---

# Software Engineering (LEAN)

**Goal:** Maintainable code following Laravel/PSR-12 conventions.

## Code Style & Patterns

- **Conventions:** PSR-12 and Laravel naming (StudlyCase, camelCase, snake_case).
- **Self-Documenting:** Prioritize clear naming and types over comments.
- **Type Safety:** MANDATORY PHP type hints and return types on all methods.
- **Clean Code:** No debug code (`dd`, `dump`). SRP (Single Responsibility).

## Development Workflow

- **Atomic:** Groups related changes (logic + tests + migrations) together.
- **CI First:** Run `./vendor/bin/pint --test` and `php artisan test` BEFORE success claims.
- **Fact-Based:** Verify framework features if unsure. No hallucinations.

## Laravel Layout

Follow standard: `Models`, `Controllers`, `Requests`, `Policies`, `Services`, `Actions`.

## Verification Checklist

- [ ] Pint/Pest compliant
- [ ] Argument/Return types present
- [ ] No hardcoded secrets
- [ ] Public interfaces documented

## References

- `references/implementation-workflow.md` - (Atomic commits and CI process)
- `references/workflow-patterns.md` - (TDD and review patterns)