# Testing the Package in a Laravel Project

This guide shows you how to test the Laravel llms.txt package in a real Laravel application with SSR routes.

## Step 1: Create a Test Laravel Project

```bash
# Create a new Laravel project
composer create-project laravel/laravel test-llms-txt
cd test-llms-txt
```

## Step 2: Install the Package Locally

Since the package isn't published yet, you can install it locally using Composer's path repository.

### Option A: Using Path Repository (Recommended)

Edit `composer.json` in your test project:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-agent-response"
        }
    ],
    "require": {
        "your-vendor/laravel-llms-txt": "@dev"
    }
}
```

Then run:
```bash
composer require your-vendor/laravel-llms-txt:@dev
```

### Option B: Using Symlink

```bash
# From your test project root
ln -s ../../laravel-agent-response vendor/your-vendor/laravel-llms-txt
```

## Step 3: Publish Configuration

```bash
php artisan vendor:publish --tag=llms-txt-config
```

## Step 4: Configure the Package

Edit `config/llms-txt.php`:

```php
return [
    'enabled' => true,
    'path' => 'llms.txt',
    'use_case' => 'docs',
    'title' => 'My Test Application',
    'description' => 'A test application demonstrating llms.txt functionality.',
    'body' => 'This application provides documentation and examples for testing the llms.txt package.',
    'sections' => [
        'Getting Started' => [
            ['title' => 'Installation', 'url' => '/docs/installation', 'notes' => 'How to install the package'],
            ['title' => 'Configuration', 'url' => '/docs/configuration', 'notes' => 'Configuration options'],
        ],
        'API Reference' => [
            ['title' => 'Endpoints', 'url' => '/api/endpoints', 'notes' => 'Available API endpoints'],
        ],
        'Optional' => [
            ['title' => 'External Docs', 'url' => 'https://laravel.com/docs', 'notes' => 'Laravel official documentation'],
        ],
    ],
    'machine_view_enabled' => true,
    'md_extension_enabled' => true,
    'machine_view_trigger' => 'all',
    'main_content_selector' => 'main',
    'cache_enabled' => true,
    'cache_max_age' => 3600,
    'cache_visibility' => 'public',
];
```

## Step 5: Create Test SSR Routes

Create some test routes in `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test SSR routes
Route::get('/pricing', function () {
    return view('pricing');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/docs/installation', function () {
    return view('docs.installation');
});

Route::get('/docs/configuration', function () {
    return view('docs.configuration');
});
```

## Step 6: Create Test Views

Create test Blade views to test the markdown conversion:

### `resources/views/pricing.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Pricing - Test App</title>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/pricing">Pricing</a>
        <a href="/about">About</a>
    </nav>
    
    <main>
        <h1>Pricing</h1>
        <p>Choose the plan that's right for you.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Features</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic</td>
                    <td>$10/month</td>
                    <td>Basic features</td>
                </tr>
                <tr>
                    <td>Pro</td>
                    <td>$30/month</td>
                    <td>All features</td>
                </tr>
            </tbody>
        </table>
        
        <h2>Features</h2>
        <ul>
            <li>Feature 1</li>
            <li>Feature 2</li>
            <li>Feature 3</li>
        </ul>
        
        <p><a href="/contact">Contact us</a> for enterprise pricing.</p>
    </main>
    
    <footer>
        <p>&copy; 2024 Test App</p>
    </footer>
</body>
</html>
```

### `resources/views/about.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>About - Test App</title>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/pricing">Pricing</a>
        <a href="/about">About</a>
    </nav>
    
    <main>
        <h1>About Us</h1>
        <p>We are a company dedicated to providing excellent service.</p>
        
        <h2>Our Mission</h2>
        <p>To help businesses succeed through innovative solutions.</p>
        
        <h2>Contact</h2>
        <p>Email: <a href="mailto:info@example.com">info@example.com</a></p>
        <p>Phone: +1 (555) 123-4567</p>
    </main>
    
    <footer>
        <p>&copy; 2024 Test App</p>
    </footer>
</body>
</html>
```

### `resources/views/docs/installation.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Installation - Test App</title>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/docs/installation">Installation</a>
    </nav>
    
    <main>
        <h1>Installation Guide</h1>
        <p>Follow these steps to install the package.</p>
        
        <h2>Step 1: Install via Composer</h2>
        <pre><code>composer require your-vendor/laravel-llms-txt</code></pre>
        
        <h2>Step 2: Publish Configuration</h2>
        <pre><code>php artisan vendor:publish --tag=llms-txt-config</code></pre>
        
        <h2>Step 3: Configure</h2>
        <p>Edit <code>config/llms-txt.php</code> with your settings.</p>
    </main>
</body>
</html>
```

## Step 7: Test the Package

### Test 1: llms.txt Endpoint

```bash
# Visit in browser or use curl
curl http://localhost:8000/llms.txt
```

Expected: Markdown content with H1, blockquote, and sections.

### Test 2: .md Extension

```bash
# Visit in browser
http://localhost:8000/pricing.md

# Or use curl
curl http://localhost:8000/pricing.md
```

Expected: Markdown version of the pricing page (only main content).

### Test 3: Query Parameter

```bash
# Visit in browser
http://localhost:8000/pricing?view=machine

# Or use curl
curl "http://localhost:8000/pricing?view=machine"
```

Expected: Markdown version of the pricing page.

### Test 4: Accept Header

```bash
curl -H "Accept: text/markdown" http://localhost:8000/pricing
```

Expected: Markdown version of the pricing page.

### Test 5: Normal HTML (Human View)

```bash
curl http://localhost:8000/pricing
```

Expected: Full HTML page with navigation, footer, etc.

### Test 6: Cache Headers

```bash
curl -I http://localhost:8000/llms.txt
```

Expected: `Cache-Control: public, max-age=3600` header.

## Step 8: Add Human/Machine Toggle (Optional)

Add this to your Blade layout to test the toggle:

```blade
<!-- Add to resources/views/layouts/app.blade.php or individual views -->
<div style="position: fixed; bottom: 20px; right: 20px; padding: 10px; background: #f0f0f0; border-radius: 5px;">
    <strong>View:</strong>
    @if(request()->query('view') === 'machine')
        <a href="{{ request()->url() }}">Human</a> | <strong>Machine</strong>
    @else
        <strong>Human</strong> | <a href="{{ request()->fullUrlWithQuery(['view' => 'machine']) }}">Machine</a>
    @endif
</div>
```

## Step 9: Verify Main Content Selector

The package should extract only the `<main>` content when converting to markdown. Verify:

1. Visit `/pricing` - see full HTML with nav and footer
2. Visit `/pricing.md` - see only main content in markdown (no nav, no footer)

## Troubleshooting

### Package not found
- Make sure the path repository is correct in `composer.json`
- Run `composer dump-autoload`

### Routes not working
- Clear route cache: `php artisan route:clear`
- Clear config cache: `php artisan config:clear`

### Markdown not converting
- Check `machine_view_enabled` is `true` in config
- Verify the response is HTML (not JSON)
- Check middleware is registered: `php artisan route:list`

### .md extension not working
- Ensure `md_extension_enabled` is `true`
- The route must be registered before other routes (it's registered in `registerMiddleware()`)
- Try accessing a simple route first: `/pricing.md`

## Quick Test Script

Create `test-package.php` in your test project root:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test llms.txt
echo "Testing /llms.txt:\n";
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/llms.txt', 'GET')
);
echo $response->getContent() . "\n\n";

// Test pricing.md
echo "Testing /pricing.md:\n";
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/pricing.md', 'GET')
);
echo $response->getContent() . "\n\n";

// Test pricing?view=machine
echo "Testing /pricing?view=machine:\n";
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/pricing?view=machine', 'GET')
);
echo $response->getContent() . "\n";
```

Run with: `php test-package.php`
