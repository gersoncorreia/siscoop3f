# Backend Architecture (LEAN)

## 1. Application Patterns
```php
// Action: Single Responsibility
class CreateUserAction {
    public function execute(CreateUserData $data): User {
        return DB::transaction(fn() => User::create($data->toArray()));
    }
}

// Service: Business Logic (Multiple Models)
class OrderService {
    public function process(Order $order) {
        $order->update(['status' => 'paid']);
        event(new OrderProcessed($order));
    }
}

// DTO: Structured Data
readonly class CreateUserData {
    public function __construct(public string $name, public string $email) {}
    public static function fromRequest(Request $r) => new self(...$r->validated());
}
```

## 2. Multi-Tenancy (Common)
- **Shared DB:** Use `BelongsToTenant` trait with a Global Scope.
- **Separate DB:** Use `stancl/tenancy` for domain-based isolation.

## 3. Modular Monolith
Organize code by domain: `app/Modules/{User, Billing, Notifications}`. Keep a clear boundary between modules via Events.

## 4. Scaling Patterns
- **Events:** Use `OrderPlaced -> [SendEmail, UpdateStock]`. Use `ShouldQueue`.
- **CQRS:** Separate Read (Query Builder/Raw) and Write (Eloquent) models for high-traffic dashboards.
- **BFF:** Use Inertia.js to tightly couple Frontend (Blade/Vue) and Backend (Laravel).
