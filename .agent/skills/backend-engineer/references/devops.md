# DevOps & Deployment (Laravel)

## 1. Development (Sail)
```bash
# Docker-based env
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

## 2. Deployment (Forge/Envoy)
- **Forge:** Automated server config (Nginx, PHP, Redis, MySQL).
- **Envoy:** Task runner for zero-downtime deployment.
```bash
# Example deployment flow
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan optimize
```

## 3. GitHub Actions (CI)
```yaml
# Essential CI steps
- name: Code Style (Pint)
  run: ./vendor/bin/pint --test
- name: Tests (Pest)
  run: php artisan test --parallel
```

## 4. Server Configuration
- **Nginx:** Configure `fpm.sock`, `client_max_body_size`, and block `.env`.
- **FPM:** Tune `pm.max_children` based on RAM.
- **Queues:** Use `Supervisor` to manage `php artisan queue:work`.

## 5. Maintenance
- **Backups:** Use `spatie/laravel-backup` for S3 off-site copies.
- **Monitoring:** Pulse, Sentry, or OhDear for uptime and exceptions.