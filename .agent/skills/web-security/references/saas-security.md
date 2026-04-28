# SaaS Multi-Tenant Security (Laravel)

## 1. Data Isolation (Global Scopes)
```php
trait BelongsToTenant {
    protected static function bootBelongsToTenant() {
        static::addGlobalScope('tenant', fn(Builder $builder) => $builder->where('tenant_id', auth()->user()->tenant_id));
        static::creating(fn($model) => $model->tenant_id = auth()->user()->tenant_id);
    }
}
```

## 2. Scoped Resource Binding
```php
// Enforce tenant scoping in routes
Route::middleware(['auth', 'tenant'])->group(function() {
    Route::get('/orders/{order}', fn(Order $order) => $order)
         ->scopeBindings(); // Scopes by parent/tenant automatically
});
```

## 3. System Isolation
- **Cache:** Prefix keys with tenant ID: `Cache::store('redis')->tags(['tenant_'.$id])`.
- **Queues:** Jobs MUST include `tenant_id` and re-apply context on execution.
- **Storage:** Use hashed/tenant-specific directories: `storage/app/tenants/{tenant_id}/`.
- **Sessions:** Scope sessions to prevent cross-account login hijacking.

## 4. Subscriptions & Webhooks
- **Access Check:** Middleware to verify subscription status before any action.
- **Webhook Integrity:** Verify all provider signatures (Stripe/Pagar.me).

## 5. Admin & Auditing
- **Super-Admin:** Log all actions performed in "Impersonation" mode.
- **Auditing:** Track `created_by`, `updated_by` for all sensitive entities.
