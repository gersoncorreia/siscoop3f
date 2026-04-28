# Implementation Workflow (LEAN)

## 1. Plan & Analyze
- Use `systems-analyst` to define the feature logic.
- Verify edge cases and state transitions.

## 2. Database First
- Use `database-architect` for migrations and Eloquent relations.
- Run `php artisan migrate`.

## 3. TDD Cycle
- Use `tdd-practitioner` for Pest tests (Red -> Green -> Refactor).
- Target logic coverage before UI.

## 4. Frontend & Polish
- Use `frontend-design` for TALL stack implementation.
- Polish with Tailwind and Alpine.js micro-interactions.

## 5. Verify & Peer Review
- Use `code-review` for final sanity check (Pint, PHPUnit).
- Update documentation and commit with logical, atomic messages.