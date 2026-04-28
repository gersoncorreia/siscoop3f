# Laravel + MySQL Design Patterns

When generating migrations and models as the `database-architect`, compare your output against these patterns to ensure maximum reliability and performance.

### 1. Migrations: The Bad AI vs. The Architect

**The Bad AI Migration (Do not do this):**
```php
public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // Missing FK, Missing Index!
        $table->string('status');              // VARCHAR(255) for a 10 char string!
        $table->float('total');                // Floating point math errors on money!
        $table->text('metadata');              // Using text for JSON!
        $table->timestamps();
    });
}
```

**The Architect Migration (DO THIS):**
```php
public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        
        // Strict Relations with explicit cascade strategy
        $table->foreignId('user_id')->constrained()->restrictOnDelete();
        $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        
        // Precise typing and indexing
        $table->string('status', 20)->index(); // Indexed for quick filtering
        $table->integer('total_cents');        // Money stored as integer (cents)
        
        // Proper JSON structure if truly needed
        $table->json('payment_gateway_response')->nullable(); 
        
        // Composite index for common tenant queries
        $table->index(['store_id', 'status']);
        
        $table->timestamps();
    });
}
```

### 2. Polymorphic Relations: Safe vs Unsafe

**Unsafe (AI Default):**
```php
$table->unsignedBigInteger('commentable_id');
$table->string('commentable_type');
// Result: Extremely slow queries as the table grows because there is no composite index!
```

**Safe (Architect Level):**
```php
// Option A: The Laravel shortcut (automatically creates the composite index)
$table->morphs('commentable');

// Option B: Manual (if you need specific lengths, e.g., UUIDs)
$table->uuid('commentable_id');
$table->string('commentable_type', 50);
$table->index(['commentable_type', 'commentable_id']); // MANDATORY COMPOSITE INDEX
```

### 3. Eloquent Model Bindings

Your Eloquent models MUST reflect the strictness of your database schema.

```php
class Order extends Model
{
    // 1. Explicit Fillable (Security)
    protected $fillable = [
        'store_id',
        'status',
        'total_cents',
        'payment_gateway_response'
    ];

    // 2. Explicit Casts (Data Integrity)
    protected $casts = [
        'total_cents' => 'integer',
        'payment_gateway_response' => 'array', // Cast JSON to Array automatically
        'created_at' => 'datetime',
    ];

    // 3. Status Constants (Avoid magic strings in code)
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    // 4. Accessors for display logic (e.g., converting cents to dollars)
    protected function totalDollars(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_cents / 100,
        );
    }
}
```

### 4. Pivot Tables (Many-to-Many)

AI usually creates pivot tables without primary keys and forgets foreign keys.

**Correct Pivot Migration:**
```php
public function up()
{
    Schema::create('role_user', function (Blueprint $table) {
        // Pivot tables often don't need independent IDs if the combination guarantees uniqueness
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('role_id')->constrained()->cascadeOnDelete();
        
        // The composite primary key prevents duplicate assignments
        $table->primary(['user_id', 'role_id']);
        
        $table->timestamps();
    });
}
```

### 5. Multi-Tenant Schemas

If building a SaaS with a single database, ALL tenant-owned tables MUST have the `tenant_id` included in their unique constraints.

```php
// BAD: A user can only have one tag named 'urgent' across the ENTIRE system.
$table->string('slug')->unique(); 

// GOOD: A user can have one tag named 'urgent' PER TENANT.
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->string('slug', 50);
$table->unique(['tenant_id', 'slug']); 
```
