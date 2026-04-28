# Pest PHP TDD Workflow

This document illustrates the strict TDD workflow expected by the `tdd-practitioner` skill. Do not write production code before following this pattern.

## The Red-Green-Refactor Cycle in Action

Let's assume the requirement from the `systems-analyst` is: *"A tenant admin can invite a new user, but only if they haven't reached their plan limit."*

### 1. RED Phase: Write the behavior we expect

We write the test *before* the controller or service class exists.

**File: `tests/Feature/Tenant/InviteUserTest.php`**
```php
<?php

use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionService;

it('prevents inviting users when tenant has reached plan limit', function () {
    // Arrange
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->for($tenant)->create(['role' => 'admin']);
    
    // We mock the subscription service to simulate a reached limit
    $this->mock(SubscriptionService::class, function ($mock) use ($tenant) {
        $mock->shouldReceive('hasReachedUserLimit')
             ->with($tenant)
             ->andReturn(true);
    });

    // Act
    $response = $this->actingAs($admin)->postJson('/api/tenant/invites', [
        'email' => 'newuser@example.com'
    ]);

    // Assert
    $response->assertStatus(403)
             ->assertJson(['message' => 'Plan user limit reached.']);
             
    $this->assertDatabaseMissing('invitations', [
        'email' => 'newuser@example.com'
    ]);
});
```

**Result of running test:** *FAIL. 404 Route Not Found (because the route doesn't exist yet).*

### 2. GREEN Phase: Write the minimal code to pass

We write *only* what is necessary to pass the test.

**File: `routes/api.php`**
```php
Route::middleware(['auth:sanctum'])->group(function() {
    Route::post('/tenant/invites', [App\Http\Controllers\TenantInviteController::class, 'store']);
});
```

**File: `app/Http/Controllers/TenantInviteController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class TenantInviteController extends Controller
{
    public function store(Request $request, SubscriptionService $subscriptions)
    {
        $tenant = $request->user()->tenant;
        
        if ($subscriptions->hasReachedUserLimit($tenant)) {
            return response()->json(['message' => 'Plan user limit reached.'], 403);
        }
        
        // At this point, the test passes. We stop writing code for the controller.
    }
}
```

**Result of running test:** *PASS.*

### 3. REFACTOR Phase: Clean up

We notice the controller logic could be moved to a Form Request Authorization layer for better separation of concerns.

**File: `app/Http/Requests/StoreTenantInviteRequest.php`**
```php
<?php

namespace App\Http\Requests;

use App\Services\SubscriptionService;
use Illuminate\Foundation\Http\FormRequest;

class StoreTenantInviteRequest extends FormRequest
{
    public function authorize(SubscriptionService $subscriptions): bool
    {
        return ! $subscriptions->hasReachedUserLimit($this->user()->tenant);
    }
    
    // ...
}
```

**File: `app/Http/Controllers/TenantInviteController.php`** (Refactored)
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantInviteRequest;

class TenantInviteController extends Controller
{
    public function store(StoreTenantInviteRequest $request)
    {
        // The authorization logic handles the 403 response automatically.
        // We run the test again. It still passes.
    }
}
```

**Result of running test after refactor:** *PASS. (Green maintained).*

### 4. Back to Phase 1 (Next Requirement)

Now we write the test for the "happy path" (successful invitation). 

```php
it('successfully invites a user and queues an email', function () {
    // Arrange...
    // Act...
    // Assert Queue::pushed(SendInvitationEmail::class)...
});
```

## Anti-Patterns to Avoid (What the AI usually does wrong)

- **Testing Implementation Details:** Don't test *how* a method works, test *what* it returns or the state it changes.
  - *Bad:* `$this->assertTrue($object->isCalculatedFlag == true);`
  - *Good:* `expect($object->calculateTotal())->toBe(150);`
- **Missing Edge Cases:** Only testing that an upload works, but forgetting to write a test asserting that a 15MB file is rejected with a 422 HTTP code.
- **Skipping Mocks for External Services:** Real HTTP calls or Payment Gateways MUST be mocked, or tests will become flaky. Use Laravel's `Http::fake()`.
