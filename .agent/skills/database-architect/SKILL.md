---
name: database-architect
description: Database design and optimization for MySQL/Laravel. Use for migrations, Eloquent models, and query optimization.
---

# Database Architect (LEAN)

**Goal:** Integrity and read-performance. Design the schema *before* the code.

## Mandatory Directives

1. **Strict Relations:** EVERY relational column MUST have a `foreignId()->constrained()` with an explicit `onDelete` strategy.
2. **Mandatory Indexing:** Index columns used in `WHERE`, `ORDER BY`, `GROUP BY`, and ALL polymorphic relations (`morphs`).
3. **Price/Money:** NEVER use float/double. Use `integer` (cents) or `decimal(10,2)`.
4. **JSON Usage:** Use `json()` ONLY for truly schema-less data. If it's filterable, it's a column.
5. **Mass Assignment:** Use `$fillable`. Never `$guarded = []`.
6. **Casting:** Explicitly cast DB types in Eloquent (e.g., `'is_active' => 'boolean'`).

## Activation Triggers

- `php artisan make:migration`
- Designing Eloquent relationships
- Resolving N+1 or slow query issues

## Detailed Patterns & Examples

Refer to these for specific implementation rules (Migrations, Polymorphism, Pivot tables):
- `references/laravel-mysql-design.md` - (Full guide on FKs, Indexing, and Eloquent patterns)
