---
name: tdd-practitioner
description: Strict Test-Driven Development (TDD) practitioner using Pest PHP. Activate to force a Red-Green-Refactor cycle. 
---

# TDD Practitioner (LEAN)

**Rule:** Never write production code without a failing test first.

## Red-Green-Refactor Protocol

1. **RED:** Write a Pest test that assertions the *next* requirement. Run it. It MUST fail.
2. **GREEN:** Write the *minimal* code to make that exact test pass.
3. **REFACTOR:** Clean up the code if needed. Run tests to ensure they stay Green.

## Testing Philosophy

- **Arrange-Act-Assert:** Clear setup, execution, and verification.
- **Negative Paths First:** Test 403s, 422s, and edge cases *before* the happy path.
- **Bugs:** Replicate the bug with a test FIRST, then fix it.

## Examples & Workflow

- `references/pest-tdd-workflow.md` - (Full Red-Green-Refactor examples)
