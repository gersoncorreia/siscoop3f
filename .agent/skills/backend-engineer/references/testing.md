# Testing Backend (LEAN)

*Note: For detailed TDD Red-Green-Refactor examples, see the `tdd-practitioner` skill.*

## 1. Feature Tests (Pest)
```php
it('allows owner to update posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();
    $this->actingAs($user)->putJson("/api/posts/{$post->id}", ['t' => 'U'])->assertOk();
});

it('validates request rules', function () {
    $this->actingAs(User::factory()->create())->postJson('/api/posts', [])->assertUnprocessable()->assertJsonValidationErrors(['title']);
});
```

## 2. Unit & Mocking
```php
it('calculates total price correctly', fn() => expect(Order::factory(['price' => 100])->create()->total())->toBe(100));

it('notifies user on creation', function () {
    Notification::fake();
    $u = User::factory()->create();
    Notification::assertSentTo($u, WelcomeNotification::class);
});
```

## 3. Database & Parallel
- **Database:** Use `RefreshDatabase` trait for clean state.
- **Parallel:** Run `php artisan test --parallel` to speed up CI.
- **Dusk:** Use for complex E2E/Browser testing with `DuskTestCase`.