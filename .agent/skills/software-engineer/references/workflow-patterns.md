# Workflow Patterns

Detailed patterns and practices for software development workflows.

## Implementation Workflow

- **Atomic changes**: Group related changes together (code + tests + migrations)
- **CI verification**: Run CI checks before staging (`php artisan test`, `./vendor/bin/pint --test`, `phpstan`)
- **Semantic commits**: Use conventional commit format
- **Stop and ask**: Ask user before adding type ignores, suppressions, or `mixed` types

## Code Review Workflow

- Review for functionality, code quality, and security
- Check edge cases and error handling
- Verify tests are present and passing (Pest/PHPUnit)
- Ensure code follows Laravel conventions (PSR-12, naming)
- Verify Form Requests for validation, Policies for authorization
- Check for N+1 queries (use eager loading)
- Verify CI passes before completion claims

## Testing Workflow

- Write tests before or alongside implementation (Pest preferred)
- Use `RefreshDatabase` trait for database tests
- Use Model Factories for test data
- Test edge cases and error conditions
- Maintain test coverage for critical paths
- Tests go with implementation in atomic commits

## Documentation Workflow

- Update documentation when code changes
- Keep README and AGENT files current and accurate
- Document non-obvious design decisions
- Include examples for complex APIs

## Refactoring Workflow

- Refactor incrementally, not in big bangs
- Maintain test coverage during refactoring
- Preserve functionality while improving structure
- Document significant architectural changes
- Feature tests serve as confirmation gate for internal refactoring