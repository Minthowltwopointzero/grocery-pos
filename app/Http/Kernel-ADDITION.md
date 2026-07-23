In your project's `app/Http/Kernel.php`, register the role middleware alias.

Find the `$routeMiddleware` (Laravel 9) or `$middlewareAliases` (Laravel 10) array and add:

```php
protected $middlewareAliases = [
    // ... existing entries (auth, guest, etc.) stay as-is
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
```

That's the only change needed to Kernel.php — everything else (auth, guest middleware) already ships with a fresh Laravel install.
