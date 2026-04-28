# Infrastructure Security (Laravel)

## 1. Environment Protection
```bash
# Nginx: Block dotfiles (.env, .git)
location ~ /\. { deny all; return 404; }

# Production .env:
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

## 2. SSL/TLS & Headers
```php
// Force HTTPS (AppServiceProvider)
if (app()->isProduction()) URL::forceScheme('https');

// HSTS & Security Headers (Nginx)
add_header X-Frame-Options "DENY";
add_header X-Content-Type-Options "nosniff";
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline';";
```

## 3. Server Hardening
- **PHP:** `expose_php = Off`, `display_errors = Off`.
- **MySQL:** Apps use non-root user with minimal privileges.
- **Firewall:** Port 22, 80, 443 only.
- **Permissions:** 644 files, 755 dirs, 775 storage/cache.

## 4. Deployment & Backups
```bash
# Production: No dev-deps, Caching active
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan optimize

# Backups: Encrypted and remote (S3)
# php artisan backup:run (spatie/laravel-backup)
```

## 5. Security Checklist
- [ ] HTTPS enforced (HSTS active)
- [ ] Security headers active
- [ ] .env not web-accessible
- [ ] APP_DEBUG=false
- [ ] Minimal MySQL privileges
- [ ] Encrypted off-site backups
