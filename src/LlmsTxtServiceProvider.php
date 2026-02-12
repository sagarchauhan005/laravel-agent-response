<?php

namespace YourVendor\LaravelLlmsTxt;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LlmsTxtServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/llms-txt.php',
            'llms-txt'
        );

        $this->app->singleton(LlmsTxtService::class, function ($app) {
            return new LlmsTxtService(new LlmsTxtWriter());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/llms-txt.php' => config_path('llms-txt.php'),
            ], 'llms-txt-config');
        }

        if (! config('llms-txt.enabled', true)) {
            return;
        }

        $this->registerMiddleware();
        $this->registerRoutes();
    }

    /**
     * Register the llms.txt route.
     */
    protected function registerRoutes(): void
    {
        $path = config('llms-txt.path', 'llms.txt');
        $path = ltrim($path, '/');

        Route::get($path, function () {
            $service = app(LlmsTxtService::class);
            $markdown = $service->generate();

            $response = response($markdown, 200, [
                'Content-Type' => 'text/markdown; charset=UTF-8',
            ]);

            // Add cache headers if enabled
            if (config('llms-txt.cache_enabled', true)) {
                $this->addCacheHeaders($response, $markdown);
            }

            return $response;
        })->middleware(config('llms-txt.middleware', ['web']));
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        // Add Link header middleware
        if (config('llms-txt.add_link_header', false)) {
            $this->app['router']->pushMiddlewareToGroup('web', Middleware\AddLlmsTxtLinkHeader::class);
        }

        // Register machine view middleware
        if (config('llms-txt.machine_view_enabled', true)) {
            $this->app['router']->pushMiddlewareToGroup('web', Middleware\ServeMachineView::class);
        }
        
        // Register .md extension route if enabled (must be before other routes)
        if (config('llms-txt.machine_view_enabled', true) && config('llms-txt.md_extension_enabled', true)) {
            $this->registerMdExtensionRoute();
        }
    }


    /**
     * Register catch-all route for .md extension.
     */
    protected function registerMdExtensionRoute(): void
    {
        // Register route pattern that matches any path ending in .md
        // This must be registered early, so it's in registerRoutes which is called in boot
        Route::get('{path}.md', function ($path) {
            $request = request();
            
            // Skip llms.txt.md
            if ($path === config('llms-txt.path', 'llms.txt')) {
                abort(404);
            }
            
            // Create a new request for the original path
            $originalRequest = Request::create('/'.$path, $request->method(), $request->all());
            $originalRequest->headers->replace($request->headers->all());
            
            // Dispatch to get the original response
            $response = $this->app->handle($originalRequest);
            
            // If it's HTML, convert to markdown
            if ($this->isHtmlResponse($response)) {
                $markdown = app(Middleware\ServeMachineView::class)->convertHtmlToMarkdown(
                    $response->getContent()
                );
                
                $markdownResponse = response($markdown, $response->status(), [
                    'Content-Type' => 'text/markdown; charset=UTF-8',
                ]);

                // Copy other headers
                foreach ($response->headers->all() as $key => $values) {
                    if (strtolower($key) !== 'content-type') {
                        foreach ($values as $value) {
                            $markdownResponse->headers->set($key, $value, false);
                        }
                    }
                }

                // Add cache headers if enabled
                if (config('llms-txt.cache_enabled', true)) {
                    $this->addCacheHeaders($markdownResponse, $markdown);
                }
                
                return $markdownResponse;
            }
            
            return $response;
        })->where('path', '.*')->middleware(config('llms-txt.middleware', ['web']));
    }

    /**
     * Check if response is HTML.
     */
    protected function isHtmlResponse($response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return str_contains(strtolower($contentType), 'text/html');
    }

    /**
     * Add cache headers to response.
     */
    protected function addCacheHeaders($response, string $content): void
    {
        $maxAge = config('llms-txt.cache_max_age', 3600);
        $visibility = config('llms-txt.cache_visibility', 'public');
        
        $cacheControl = sprintf('%s, max-age=%d', $visibility, $maxAge);
        $response->headers->set('Cache-Control', $cacheControl);

        if (config('llms-txt.cache_etag', false)) {
            $etag = md5($content);
            $response->headers->set('ETag', '"'.$etag.'"');
        }
    }
}
