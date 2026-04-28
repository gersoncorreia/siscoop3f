# Backend Performance (LEAN)

## 1. Database Optimization
- **N+1 Prevention:** ALWAYS use `Eager Loading` with `with()`.
- **Query Caching:** `Cache::remember('k', 60, fn() => Post::active()->get())`.
- **Chunking:** Use `chunk()` or `cursor()` for large datasets.
- **Indexes:** Ensure columns in `WHERE` and `ORDER BY` are indexed (MySQL).

## 2. Application Caching
```php
// Route & Config Cache (Production Only)
php artisan route:cache
php artisan config:cache

// Redis: Default for cache, sessions, and queues.
CACHE_STORE=redis
```

## 3. Background Processing
- **Queues:** Move Emails, Image Processing, and API calls to Jobs.
- **Throttling:** Use `RateLimiter` to protect heavy endpoints.

## 4. Modern Stack (Vite & Octane)
- **Octane:** High-performance serving via Swoole or RoadRunner.
- **Pulse:** Real-time application monitoring and bottleneck identification.