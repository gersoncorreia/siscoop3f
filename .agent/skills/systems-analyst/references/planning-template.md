# Feature Planning Template

Whenever you act as the `systems-analyst`, you MUST generate a document following this exact structure before writing any implementation code.

---

# Plan: [Feature Name]

## 1. Objective & Scope
*Define exactly what this feature does. In 2-3 sentences, what is the business value?*
*Define exactly what is OUT OF SCOPE for this specific iteration.*

## 2. Business Rules & Logic
*List the core rules that must be enforced.*
- Rule 1: [e.g., A user cannot delete an invoice if it has already been paid.]
- Rule 2: [e.g., Trial users can only create up to 5 projects.]

## 3. Edge Cases & Unhappy Paths (CRITICAL)
*Map out everything that can go wrong and how the system should respond. AI often forgets this section.*
- **Scenario:** The external payment gateway times out.
  - **Resolution:** The job moves to a pending queue and retries 3 times with exponential backoff.
- **Scenario:** Two users attempt to claim the same ticket at the exact same millisecond.
  - **Resolution:** Use a database transaction with pessimistic locking (`lockForUpdate()`).

## 4. State Machine (If applicable)
*If manipulating an entity with statuses (e.g., Orders, Tickets, Tasks).*
| Current State | Action/Trigger | New State | Side Effects (Jobs/Emails) |
|---------------|----------------|-----------|----------------------------|
| Draft | User hits 'Publish' | Published | Dispatch `NotifySubscribersJob` |

## 5. Security & Permissions
*Who can do what? Think in terms of Laravel Policies.*
- **View:** [e.g., The owner of the record, or any Admin]
- **Create:** [e.g., Any authenticated user with an active subscription]
- **Update:** [e.g., Only the owner. Admins cannot edit, only delete.]
- **Delete:** [e.g., Only the owner, and only if status is 'Draft']
- **Multi-Tenant Isolation:** [e.g., All queries must be scoped by `tenant_id`]

## 6. Architectural Impact
*How does this affect the existing system?*
- **Database Schema Changes:** (Briefly list new tables or columns. Leave exact column types for the `database-architect`).
- **New Routes/Endpoints:** (List the REST/Web routes needed).
- **Background Jobs:** (Will this feature require queues? Scheduled commands?)
- **Caching:** (Does this introduce slow queries that need caching? How is the cache invalidated?)

## 7. Testing Strategy
*Briefly list the critical test cases that the `tdd-practitioner` must write.*
- [ ] Test the happy path.
- [ ] Test that unauthorized users receive a 403.
- [ ] Test Edge Case X from Section 3.

---

*(Agent Note: Present this plan to the user in a `notify_user` block and EXPLICITLY ask for approval or modifications before proceeding to the code.)*
