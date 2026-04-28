# Implementation Workflow

Unified workflow for implementing code changes with quality gates and atomic commits.

## Workflow Pattern Detection

Before implementing, check for existing workflow patterns in the repository and your skills.

### Spec-First Workflow
- Look for `docs/` or `specs/` directories with specifications
- Check for spec files in project structure
- Follow spec → implement pattern if present

### Test-Driven Development (TDD)
- Check if tests are written before implementation (Pest/PHPUnit)
- Look for test-first patterns in codebase
- Follow test → implement → refactor cycle if present

### Other Structured Workflows
- Check for plan files or structured documentation
- Look for workflow indicators in README.md or AGENTS.md
- Follow existing patterns and skills when detected

## Implementation Process

### 1. Atomic Changes

Group related changes together:
- Implementation code + tests
- Feature + migration + factory
- Fix + test for fix

Each atomic change should:
- Pass `php artisan test` (or equivalent)
- Pass `./vendor/bin/pint --test` (code style)
- Pass `./vendor/bin/phpstan analyse` (if configured)
- Be self-consistent

### 2. CI Verification

Before staging any changes:
1. Run CI checks (`php artisan test`, `./vendor/bin/pint --test`, `phpstan`)
2. Prefer single CI command if available (`composer check`)
3. If checks fail, stop and report
4. Only proceed when all checks pass

### 3. Atomic Commits

After CI passes:
1. Stage atomic changes
2. Suggest semantic commit message
3. Confirm with user
4. Commit after approval
5. Continue to next atomic change

## Stop and Ask

Stop and ask user before:
- Adding type ignores, suppressions, or `@phpstan-ignore` annotations
- Using `mixed` type or type escapes
- Uncertain how to proceed
- Requirements unclear
- Better approach needs confirmation

## Backward Compatibility

- **Public interfaces**: Consider backward compatibility for APIs, packages
- **Internal refactoring**: Unit, integration, and feature tests serve as confirmation gate
- **Greenfield**: No backward compatibility needed unless directed

## Automation

If user requests "continue to X":
- Proceed with atomic commits automatically
- Resume asking when X point reached
- X can be: file, feature, test passing, etc.

## Integration

This workflow integrates with:
- CI verification before commits
- Semantic commit messages
- Pest/PHPUnit test-first development when present
- Code review practices
- Laravel Pint code style enforcement