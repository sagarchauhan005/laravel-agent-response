<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the llms.txt package functionality.
    |
    */

    'enabled' => env('LLMS_TXT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | The route path for the llms.txt endpoint. Default is 'llms.txt'.
    | This will be accessible at /llms.txt when enabled.
    |
    */

    'path' => env('LLMS_TXT_PATH', 'llms.txt'),

    /*
    |--------------------------------------------------------------------------
    | Use Case
    |--------------------------------------------------------------------------
    |
    | The use case preset to use. Options: 'docs', 'business', 'ecommerce',
    | 'education', 'legislation', 'custom'.
    |
    */

    'use_case' => env('LLMS_TXT_USE_CASE', 'custom'),

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | The site/project name (H1 in llms.txt). Defaults to app name.
    |
    */

    'title' => env('LLMS_TXT_TITLE', config('app.name')),

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    |
    | Short summary of the project (blockquote in llms.txt).
    | Required by llms.txt spec.
    |
    */

    'description' => env('LLMS_TXT_DESCRIPTION', ''),

    /*
    |--------------------------------------------------------------------------
    | Body
    |--------------------------------------------------------------------------
    |
    | Optional markdown content between description and sections.
    |
    */

    'body' => env('LLMS_TXT_BODY', ''),

    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    |
    | Array of sections with links. Each section has:
    | - Key: section heading (e.g. 'Docs', 'Optional')
    | - Value: array of links with 'title', 'url', and optional 'notes'
    |
    | Special section key 'optional' or 'Optional' is treated specially
    | per llms.txt spec.
    |
    */

    'sections' => [
        // Example:
        // 'Docs' => [
        //     ['title' => 'Getting Started', 'url' => '/docs/getting-started', 'notes' => 'Quick start guide'],
        // ],
        // 'Optional' => [
        //     ['title' => 'External Reference', 'url' => 'https://example.com', 'notes' => 'Additional resources'],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to the llms.txt route.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Add Link Header
    |--------------------------------------------------------------------------
    |
    | If true, adds Link header to responses pointing to /llms.txt
    | for agent discovery.
    |
    */

    'add_link_header' => env('LLMS_TXT_ADD_LINK_HEADER', false),

    /*
    |--------------------------------------------------------------------------
    | Machine View
    |--------------------------------------------------------------------------
    |
    | Configuration for per-route Human/Machine markdown views.
    |
    */

    'machine_view_enabled' => env('LLMS_TXT_MACHINE_VIEW_ENABLED', true),

    'md_extension_enabled' => env('LLMS_TXT_MD_EXTENSION_ENABLED', true),

    'machine_view_trigger' => env('LLMS_TXT_MACHINE_VIEW_TRIGGER', 'all'), // 'query', 'accept', 'header', 'all'

    'main_content_selector' => env('LLMS_TXT_MAIN_CONTENT_SELECTOR', 'main'), // e.g. 'main', '#content', '.prose', null

    // Maximum size of HTML (in bytes/characters) to attempt converting to markdown.
    // Larger responses will be truncated and annotated in the machine view output
    // so that agents know to fetch the full HTML if needed.
    'machine_view_max_html_length' => env('LLMS_TXT_MACHINE_VIEW_MAX_HTML_LENGTH', 500000),

    /*
    |--------------------------------------------------------------------------
    | Cache Headers
    |--------------------------------------------------------------------------
    |
    | Configuration for HTTP cache headers on markdown responses.
    |
    */

    'cache_enabled' => env('LLMS_TXT_CACHE_ENABLED', true),

    'cache_max_age' => env('LLMS_TXT_CACHE_MAX_AGE', 3600), // seconds

    'cache_visibility' => env('LLMS_TXT_CACHE_VISIBILITY', 'public'), // 'public' or 'private'

    'cache_etag' => env('LLMS_TXT_CACHE_ETAG', false),

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    |
    | Use case presets that provide default structure and labels.
    | These are merged with the main config when a use_case is selected.
    |
    */

    'presets' => [
        'docs' => [
            'title' => null, // null means use main config
            'description' => 'Documentation and API for LLMs and IDEs.',
            'body' => null,
            'sections' => [
                'Getting Started' => [],
                'API Reference' => [],
                'Optional' => [],
            ],
        ],
        'business' => [
            'title' => null,
            'description' => 'Company/personal website with structure, policies, and contact information.',
            'body' => null,
            'sections' => [
                'About' => [],
                'Policies' => [],
                'Contact' => [],
                'Optional' => [],
            ],
        ],
        'ecommerce' => [
            'title' => null,
            'description' => 'E-commerce site with products, policies, shipping, returns, and support information.',
            'body' => null,
            'sections' => [
                'Products' => [],
                'Policies' => [],
                'Support' => [],
                'Optional' => [],
            ],
        ],
        'education' => [
            'title' => null,
            'description' => 'Educational platform with courses, resources, and learning materials.',
            'body' => null,
            'sections' => [
                'Courses' => [],
                'Resources' => [],
                'Optional' => [],
            ],
        ],
        'legislation' => [
            'title' => null,
            'description' => 'Structured overview of legislation for stakeholders.',
            'body' => null,
            'sections' => [
                'Overview' => [],
                'Sections' => [],
                'Optional' => [],
            ],
        ],
        'custom' => [
            // No defaults, everything comes from main config
        ],
    ],
];
