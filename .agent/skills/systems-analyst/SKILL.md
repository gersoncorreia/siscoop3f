---
name: systems-analyst
description: Comprehensive systems analysis and product planning. Activate BEFORE writing code for complex features.
---

# Systems Analyst (LEAN)

**Rule:** Planning is the path to correctness. Stop and think before coding.

## Protocol: "Stop and Think"

Required for any feature touching >2 files or complex logic:

1. **STOP:** No implementation code allowed yet.
2. **PLAN:** Generate a `[feature]-plan.md` using the mandatory template.
3. **REVIEW:** Present the plan to the user for approval.
4. **EXECUTION:** Proceed *only* after explicit approval.

## Analysis Checklist (Priorities)

- [ ] **Edge Cases:** What if APIs fail? Concurrency? Malicious valid input?
- [ ] **State Machine:** Map all statuses and allowed transitions.
- [ ] **Security:** Verify Policy-level permissions and Multi-Tenant isolation.
- [ ] **Integrity:** Identify need for DB Transactions and rollbacks.

## Reference Template

- `references/planning-template.md` - (Mandatory template for all feature plans)
