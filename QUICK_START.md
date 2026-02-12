# Quick Start Guide

## Testing in a Laravel Project

### 1. Install Locally

In your test Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/laravel-agent-response"
        }
    ],
    "require": {
        "sagarchauhan005/laravel-llms-txt": "@dev"
    }
}
```

```bash
composer require sagarchauhan005/laravel-llms-txt:@dev
php artisan vendor:publish --tag=llms-txt-config
```

### 2. Add Test Route

In `routes/web.php`:

```php
Route::get('/test', function () {
    return '<html><body><main><h1>Test Page</h1><p>This is a test.</p></main></body></html>';
});
```

### 3. Test It

```bash
# Start server
php artisan serve

# Test llms.txt
curl http://localhost:8000/llms.txt

# Test .md extension
curl http://localhost:8000/test.md

# Test query parameter
curl "http://localhost:8000/test?view=machine"
```

All three should return markdown!
