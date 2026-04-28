# Backend Security (LEAN)

*Note: For detailed OWASP/LGPD/SaaS patterns, see the `web-security` skill.*

## 1. Input & Validation
```php
// Use Form Requests for EVERY endpoint
public function rules(): array {
    return [
        'email' => 'required|email|unique:users',
        'password' => ['required', Password::min(8)->uncompromised()],
    ];
}

// NEVER trust $request->all(). Use validated() only.
User::create($request->validated());
```

## 2. Authorization (Policies)
```php
// Policy: Auth + Ownership
public function view(User $u, Post $p) => $u->id === $p->user_id || $u->hasRole('admin');

// Controller: Mandatory sign-off
$this->authorize('view', $post);
```

## 3. Eloquent Safety
- **No Raw Concatenation:** `whereRaw('id = ?', [$id])` (Safe) vs `whereRaw("id = $id")` (Dangerous).
- **Mass Assignment:** Whitelist via `$fillable`. Never use `$guarded = []`.
- **Casting:** Secure DB types: `'is_active' => 'boolean'`, `'meta' => 'encrypted:json'`.

## 4. Protection Layers
- **Throttling:** `RateLimiter::for('api', fn($r) => Limit::perMinute(60)->by($r->user()?->id))`.
- **CSRF:** Built-in `@csrf` for Blade. API routes use Sanctum stateful headers.
- **Logging:** Log auth failures and 403s. Mask PII in logs.
- **Secrets:** Use `config()`, never `env()` in code. `secrets.json` for CI.