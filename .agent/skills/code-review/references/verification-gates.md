# Verification Gates

## The Iron Law

**NO COMPLETION CLAIMS WITHOUT FRESH VERIFICATION EVIDENCE**

## Gate Function

IDENTIFY command → RUN full command → READ output → VERIFY confirms claim → THEN claim

Skip any step = not verifying

## Requirements

- **Tests pass:** Test output shows 0 failures
- **Build succeeds:** Build command exit 0
- **Bug fixed:** Test original symptom passes
- **Requirements met:** Line-by-line checklist verified

## Red Flags - STOP

- Using "should"/"probably"/"seems to"
- Expressing satisfaction before verification
- Committing without verification
- Trusting agent reports without evidence
- ANY wording implying success without running verification

## Evidence Format

**Bad - No evidence:**
```
"Tests should pass now."
"Build probably works."
"Looks good to me."
```

**Good - With evidence:**
```
"Tests pass: Ran `npm test`, output shows 0 failures."
"Build succeeds: `npm run build` completed with exit code 0."
"Bug fixed: Original test case now passes."
```

## Integration

- Before committing
- Before pushing
- Before creating PRs
- Before moving to next task
- Before any completion claim