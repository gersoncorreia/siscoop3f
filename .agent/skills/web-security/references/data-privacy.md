# LGPD & Data Privacy (Laravel)

## 1. PII Management
```php
// Model: Encrypt and Hide PII
protected $casts = ['cpf' => 'encrypted', 'phone' => 'encrypted'];
protected $hidden = ['cpf', 'phone', 'address'];

// Data Classification:
// - Personal: Email, Address (Encrypt)
// - Sensitive: CPF, Health (Encrypt + Audit)
```

## 2. Masking & Logging
```php
// Mask PII in UI
public function getMaskedCpfAttribute() {
    $cpf = decrypt($this->attributes['cpf']);
    return substr($cpf,0,3).'***'.substr($cpf,-2);
}

// Logs: NEVER log PII
Log::info('User action', ['user_id' => $user->id]); // Safe
```

## 3. Consent Management
```php
// Record: timestamp, purpose, IP, user agent
UserConsent::create(['user_id' => $u->id, 'purpose' => 'marketing', 'granted' => true, 'ip' => request()->ip()]);

// Check: before processing
if ($user->consents()->where('purpose', 'marketing')->active()->exists()) { ... }
```

## 4. Data Subject Rights (LGPD)
```php
// Export: JSON machine-readable
public function export(User $u) { return ['personal' => $u->only('name','email'), 'consents' => $u->consents]; }

// Deletion/Anonymization: remove PII, keep financial/legal records anonymized
$user->update(['name' => 'Deleted', 'email' => 'del@anon.com', 'cpf' => null]);
$user->delete(); // Soft-delete
```

## 5. Retention & Cleanup
```php
// Cleanup: Schedule::command('data:cleanup')->daily();
User::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete();
ActivityLog::where('created_at', '<', now()->subYear())->delete();
```

## 6. Privacy Checklist
- [ ] Encrypt PII at rest (`encrypted` cast)
- [ ] No PII in logs (use `user_id`)
- [ ] Mask data in UI
- [ ] Functionality for Data Export & Erasure
- [ ] Consent recording active
- [ ] Data retention policy implemented
