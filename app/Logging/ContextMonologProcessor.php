<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class ContextMonologProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = [
            'tenant_id' => 'central',
            'user_id' => 'guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'cli',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ];

        // Safely check for tenancy if function exists and service is ready
        if (function_exists('tenant') && app()->bound(\Stancl\Tenancy\Tenancy::class)) {
            $tenant = tenant();
            if ($tenant) {
                $context['tenant_id'] = $tenant->getTenantKey();
            }
        }

        // Safely check for auth only if it has been resolved
        if (app()->bound('auth')) {
            $auth = app('auth');
            // We use method_exists or try-catch for extra safety during boot crashes
            try {
                if ($auth->check()) {
                    $context['user_id'] = $auth->id();
                }
            } catch (\Throwable $e) {
                // Ignore errors during check() if auth is not fully ready
            }
        }

        // Safely check for request if bound
        if (app()->bound('request')) {
            $request = app('request');
            $context['url'] = $request->fullUrl();
            $context['method'] = $request->method();
            $context['ip'] = $request->ip();
        }

        return $record->with(extra: array_merge($record->extra, $context));

        return $record->with(extra: array_merge($record->extra, $context));
    }
}
