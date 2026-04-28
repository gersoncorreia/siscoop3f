# Web Security Patterns (Laravel)

## 1. Access Control (Policies)
```php
// Policy: Auth and ownership/admin check
public function update(User $user, Post $post): bool {
    return $user->tenant_id === $post->tenant_id && ($user->id === $post->user_id || $user->hasRole('admin'));
}

// Controller: Mandatory authorization
public function update(UpdatePostRequest $request, Post $post) {
    $this->authorize('update', $post); 
    $post->update($request->validated());
    return new PostResource($post);
}

// Routing: Group security
Route::middleware(['auth:sanctum', 'verified'])->group(fn() => Route::apiResource('posts', PostController::class));
```

## 2. Injection & Mass Assignment
```php
// SQL: Always use bindings/Eloquent
User::whereRaw('email = ?', [$email])->first(); // Safe

// Command: Avoid shell exec. Use Process.
Process::run(['convert', $validatedFilename, 'output.pdf']);

// Mass Assignment: Use validated() only
User::create($request->validated()); 

// Model: fillable whitelist
protected $fillable = ['name', 'email', 'password'];
```

## 3. Cryptography & Data
```php
// Casts: Encrypt at rest
protected $casts = ['cpf' => 'encrypted', 'address' => 'encrypted:array'];

// Plaintext: NEVER store or log PII
Log::info('Action', ['user_id' => $user->id]); // OK
```

## 4. Auth & Session
```php
// Throttling: sensitive routes
RateLimiter::for('login', fn(Request $r) => [Limit::perMinute(5)->by($r->ip())]);

// Passwords: complexity and compromised check
'password' => ['required', Password::min(8)->mixedCase()->uncompromised()],

// Config: secure sessions
'secure' => true, 'http_only' => true, 'same_site' => 'lax'
```

## 5. File Uploads (Private & Validated)
```php
// Request: MIME + Extension check
return ['file' => 'required|file|max:10240|mimes:pdf,jpg,png|mimetypes:application/pdf,image/jpeg'];

// Store: Random name on private disk
$path = $request->file('file')->store('uploads/'.auth()->id(), 's3');
$url = Storage::temporaryUrl($path, now()->addHours(1));
```

## 6. Integrity & SSRF
```php
// Signatures: Verify webhooks/signed URLs
if (!$request->hasValidSignature()) abort(401);

// SSRF: Whitelist domains / block internal IPs
if (!in_array(parse_url($url)['host'], ['api.example.com'])) abort(403);
```

## 7. XSS & CORS
```php
// Blade: Always escape {{ $data }}. Use @json for JS context.
{!! clean($user->bio) !!} // Sanitized via mews/purifier

// CORS: Specific origins only
'allowed_origins' => [env('FRONTEND_URL')], 'supports_credentials' => true
```
