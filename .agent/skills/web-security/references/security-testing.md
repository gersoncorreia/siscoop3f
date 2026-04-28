# Security Testing (Pest PHP)

## 1. Auth & Authorization
```php
it('denies unauthenticated access', fn() => $this->getJson('/api/posts')->assertUnauthorized());

it('prevents cross-user updates', function () {
    $user = User::factory()->create();
    $otherPost = Post::factory()->create();
    $this->actingAs($user)->putJson("/api/posts/{$otherPost->id}", ['t' => 'H'])->assertForbidden();
});

it('allows owners to update', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();
    $this->actingAs($user)->putJson("/api/posts/{$post->id}", ['t' => 'U'])->assertOk();
});
```

## 2. Multi-Tenant Isolation
```php
it('enforces tenant data isolation', function () {
    $t1 = Tenant::factory()->create();
    $t2 = Tenant::factory()->create();
    $u1 = User::factory()->for($t1)->create();
    $p2 = Post::factory()->for($t2)->create();

    $this->actingAs($u1)->getJson("/api/posts/{$p2->id}")->assertNotFound();
});

it('auto-assigns tenant on creation', function () {
    $user = User::factory()->create(['tenant_id' => 1]);
    $this->actingAs($user)->postJson('/api/posts', ['title' => 'T'])->assertCreated();
    $this->assertDatabaseHas('posts', ['title' => 'T', 'tenant_id' => 1]);
});
```

## 3. Input & File Security
```php
it('blocks SQL injection attempts', fn() => $this->postJson('/api/login', ['email' => "' OR 1=1"])->assertUnprocessable());

it('rejects executable file uploads', function () {
    $file = UploadedFile::fake()->create('malware.php', 100);
    $this->actingAs(User::factory()->create())->postJson('/api/docs', ['file' => $file])->assertUnprocessable();
});
```

## 4. Rate Limiting & Mass Assignment
```php
it('throttles excessive login attempts', function () {
    for ($i = 0; $i < 6; $i++) $this->postJson('/api/login', ['email' => 't@t.com']);
    $this->postJson('/api/login', ['email' => 't@t.com'])->assertStatus(429);
});

it('prevents mass assignment of role/admin', function () {
    $this->postJson('/api/register', ['name' => 'T', 'is_admin' => true]);
    expect(User::whereName('T')->first()->is_admin)->toBeFalse();
});
```

## 5. Verification Tools
```bash
composer audit             # Dependency vulnerabilities
php artisan test           # Suite execution
./vendor/bin/phpstan       # Static analysis
```
