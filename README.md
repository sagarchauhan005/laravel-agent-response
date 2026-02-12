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

## Philosophy and best practices

llms.txt is meant to be a **high‑signal index for agents**, not an exhaustive dump of every URL on your site.
This package is designed around a few principles:

- **Curated, not crawled**: You explicitly choose the most important pages (docs, pricing, policies, key flows)
  instead of auto‑discovering every route. This keeps the file small, readable, and cheap to consume in tokens.
- **Stable entry points**: llms.txt should highlight URLs that are unlikely to change often (collections, category
  indexes, key docs, sitemap URLs) rather than every product page or blog post.
- **One source of truth**: All content comes from `config/llms-txt.php` (plus your own extensions if you want),
  so you can review changes in code review and keep it in version control.

### Recommended setup

- **Start small**:
  - Add a handful of sections (e.g. `Getting Started`, `Products`, `Policies`, `Support`).
  - Link to category/index pages, not every individual item.
- **Use presets**:
  - Pick the closest `use_case` (`docs`, `business`, `ecommerce`, `education`, `legislation`) and then
    fill in the `sections` for that shape rather than designing your own from scratch.
- **Keep it human‑readable**:
  - Use clear titles and short `notes` so humans and agents both understand why a link matters.
  - Avoid dumping raw query URLs, deep pagination, or “internal only” tools.
- **Limit size**:
  - Prefer linking to sitemaps (`/sitemap.xml`, `/products-sitemap.xml`) or collection pages for huge catalogs.
  - If you generate links dynamically (e.g. from products/categories), limit to featured/top N items.

In practice, treat llms.txt like a **README for agents**: the place you intentionally point them at the
best starting points instead of making them guess or crawl the entire site.

### Dynamic content examples

For larger, dynamic sites (like e‑commerce), you can keep the llms.txt philosophy and still generate parts
of it from your database:

- **Products section**: instead of every SKU, expose just featured/top N products:

  ```php
  // In a custom LlmsTxtService in your app
  $config['sections']['Products'] = Product::query()
      ->where('is_featured', true)
      ->limit(100)
      ->get()
      ->map(fn ($product) => [
          'title' => $product->name,
          'url'   => route('product.show', $product),
          'notes' => $product->short_description,
      ])
      ->all();
  ```

- **Categories/Collections section**: list stable entry points:

  ```php
  $config['sections']['Categories'] = Category::query()
      ->orderBy('name')
      ->get()
      ->map(fn ($category) => [
          'title' => $category->name,
          'url'   => route('category.show', $category),
      ])
      ->all();
  ```

- **Sitemaps**: for very large catalogs, add links to your sitemaps instead of every product:

  ```php
  $config['sections']['Optional'][] = [
      'title' => 'Product sitemap',
      'url'   => url('/products-sitemap.xml'),
      'notes' => 'All product URLs for crawlers and agents',
  ];
  ```

This keeps llms.txt concise and high‑value, while still giving agents a path to the full structure.

## Installation

```bash
composer require sagarchauhan005/laravel-llms-txt
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

### Minimal setup (recommended defaults)

For most apps you only need a few env vars; everything else can stay in `config/llms-txt.php`:

```env
LLMS_TXT_ENABLED=true
LLMS_TXT_DESCRIPTION="Short, high-signal summary of what this site is for"
LLMS_TXT_USE_CASE=docs   # or: business / ecommerce / education / legislation / custom
LLMS_TXT_TITLE="My App"  # optional; falls back to APP_NAME
```

Then define your actual links in `config/llms-txt.php` under the `sections` key.

### Advanced tuning (optional)

Only reach for these when you have a concrete reason (performance, SEO, or custom behavior):

```env
# Endpoint / routing
LLMS_TXT_PATH=llms.txt

# Machine view behavior
LLMS_TXT_MACHINE_VIEW_ENABLED=true
LLMS_TXT_MD_EXTENSION_ENABLED=true
LLMS_TXT_MACHINE_VIEW_TRIGGER=all   # query | accept | header | all
LLMS_TXT_MAIN_CONTENT_SELECTOR=main # e.g. main, #content, .prose

# Caching
LLMS_TXT_CACHE_ENABLED=true
LLMS_TXT_CACHE_MAX_AGE=3600
LLMS_TXT_CACHE_VISIBILITY=public    # public | private
LLMS_TXT_CACHE_ETAG=false

# Discovery nicety
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
