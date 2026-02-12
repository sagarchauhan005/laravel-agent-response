# Laravel llms.txt Package

A Laravel package that implements the [llms.txt standard](https://llmstxt.org/) to help agents scrape and read SSR web pages more easily, in fewer tokens, with the most useful information.

## Features

- **`/llms.txt` endpoint**: Serves a standards-compliant llms.txt file with curated links and information
- **Per-route Human/Machine views**: Automatically generate markdown versions of any route (like [parallel.ai/pricing](https://parallel.ai/pricing))
  - **`.md` extension**: Access `/pricing.md` to get markdown version of `/pricing`
  - **Query parameter**: Use `?view=machine` or `?format=markdown`
  - **Accept header**: Send `Accept: text/markdown` header
- **Use-case presets**: Pre-configured templates for Docs, Business, E-commerce, Education, and Legislation
- **Cache headers**: Configurable HTTP cache headers for all markdown responses
- **Main content extraction**: Optionally extract only main content (e.g. `main`, `.prose`) to reduce tokens

## Installation

```bash
composer require your-vendor/laravel-llms-txt
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=llms-txt-config
```

## Configuration

Edit `config/llms-txt.php`:

```php
return [
    'enabled' => true,
    'path' => 'llms.txt',
    'use_case' => 'docs', // 'docs', 'business', 'ecommerce', 'education', 'legislation', 'custom'
    'title' => config('app.name'),
    'description' => 'Your project description here',
    'sections' => [
        'Getting Started' => [
            ['title' => 'Quick Start', 'url' => '/docs/quick-start', 'notes' => 'Get started in 5 minutes'],
        ],
        'Optional' => [
            ['title' => 'External Docs', 'url' => 'https://example.com/docs'],
        ],
    ],
    'machine_view_enabled' => true,
    'md_extension_enabled' => true,
    'main_content_selector' => 'main', // CSS selector for main content
    'cache_enabled' => true,
    'cache_max_age' => 3600, // 1 hour
];
```

## Usage

### Basic llms.txt

Once configured, visit `/llms.txt` to see your llms.txt file.

### Human/Machine Views

The package automatically provides markdown versions of your routes:

**Option 1: `.md` extension**
```
GET /pricing.md → Returns markdown version of /pricing
GET /about.md → Returns markdown version of /about
```

**Option 2: Query parameter**
```
GET /pricing?view=machine → Returns markdown version
GET /pricing?format=markdown → Returns markdown version
```

**Option 3: Accept header**
```
GET /pricing
Accept: text/markdown
```

### Adding a Human/Machine Toggle

Add this to your Blade layout:

```blade
<div>
    <a href="{{ request()->fullUrlWithQuery(['view' => 'human']) }}">Human</a>
    <a href="{{ request()->fullUrlWithQuery(['view' => 'machine']) }}">Machine</a>
</div>
```

Or use a simple toggle:

```blade
@if(request()->query('view') === 'machine')
    <a href="{{ request()->url() }}">Human View</a>
@else
    <a href="{{ request()->fullUrlWithQuery(['view' => 'machine']) }}">Machine View</a>
@endif
```

## Use Case Presets

### Docs/APIs
```php
'use_case' => 'docs',
```
Provides sections: Getting Started, API Reference, Optional

### Business/Personal
```php
'use_case' => 'business',
```
Provides sections: About, Policies, Contact, Optional

### E-commerce
```php
'use_case' => 'ecommerce',
```
Provides sections: Products, Policies, Support, Optional

### Education
```php
'use_case' => 'education',
```
Provides sections: Courses, Resources, Optional

### Legislation
```php
'use_case' => 'legislation',
```
Provides sections: Overview, Sections, Optional

## Environment Variables

```env
LLMS_TXT_ENABLED=true
LLMS_TXT_PATH=llms.txt
LLMS_TXT_USE_CASE=custom
LLMS_TXT_TITLE="My App"
LLMS_TXT_DESCRIPTION="Description here"
LLMS_TXT_MACHINE_VIEW_ENABLED=true
LLMS_TXT_MD_EXTENSION_ENABLED=true
LLMS_TXT_MAIN_CONTENT_SELECTOR=main
LLMS_TXT_CACHE_ENABLED=true
LLMS_TXT_CACHE_MAX_AGE=3600
LLMS_TXT_CACHE_VISIBILITY=public
LLMS_TXT_ADD_LINK_HEADER=false
```

## Testing

### Running Package Tests

```bash
composer test
```

### Testing in a Laravel Project

See [TESTING.md](TESTING.md) for detailed instructions on testing the package in a real Laravel application with SSR routes.

Quick start:
1. Install the package locally using Composer path repository
2. Publish config: `php artisan vendor:publish --tag=llms-txt-config`
3. Create test routes and views
4. Test `/llms.txt`, `/your-route.md`, and `/your-route?view=machine`

## Requirements

- PHP ^8.0
- Laravel ^8.0|^9.0|^10.0|^11.0

## License

MIT

## Credits

- Inspired by the [llms.txt specification](https://llmstxt.org/)
- Human/Machine view concept inspired by [parallel.ai](https://parallel.ai/pricing)

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.
