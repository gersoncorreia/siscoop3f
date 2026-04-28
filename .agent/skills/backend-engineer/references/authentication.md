# Authentication (Laravel)

## 1. Sanctum (API Tokens)
```php
// SPA/API Auth
$user->createToken('token-name')->plainTextToken;

// Middleware Protection
Route::middleware('auth:sanctum')->get('/user', fn(Request $r) => $r->user());
```

## 2. Fortify (Headless Auth)
- **Features:** Registration, Password Reset, Email Verification, 2FA.
- **Config:** `config/fortify.php`. Use `Fortify::loginView()` to define custom Blade/Vue views.

## 3. Socialite (OAuth)
```php
return Socialite::driver('google')->redirect();
$user = Socialite::driver('google')->user();
```

## 4. Authorization (Policies)
- **Gate:** Simple check: `Gate::authorize('admin')`.
- **Policy:** Class-based: `php artisan make:policy PostPolicy --model=Post`.
- **Implementation:** `$this->authorize('update', $post)` or `@can('update', $post)`.

## 5. Security Checklist
- [ ] 2FA active for Admin/Financial roles.
- [ ] Session regeneration on login.
- [ ] Session cookies: `secure => true`, `http_only => true`, `same_site => lax`.
- [ ] Scoped tokens with expiration.