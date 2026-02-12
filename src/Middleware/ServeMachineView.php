<?php

namespace Sagarchauhan005\LaravelLlmsTxt\Middleware;

use Closure;
use Illuminate\Http\Request;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\DomCrawler\Crawler;

class ServeMachineView
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check enabled at runtime for tests
        if (! config('llms-txt.machine_view_enabled', true)) {
            return $next($request);
        }

        $response = $next($request);

        // Skip if not HTML response
        if (! $this->isHtmlResponse($response)) {
            return $response;
        }

        // Skip llms.txt route itself
        if ($request->path() === config('llms-txt.path', 'llms.txt')) {
            return $response;
        }

        // Check if this is a "Machine" request (via .md extension, query, accept, or header)
        if (! $this->isMachineRequest($request)) {
            return $response;
        }

        // Convert HTML to markdown
        $html = $response->getContent();
        $markdown = $this->convertHtmlToMarkdown($html);

        // Create new response with markdown
        $markdownResponse = response($markdown, $response->status(), [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);

        // Copy other headers (except Content-Type)
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

    /**
     * Check if response is HTML.
     *
     * @param  mixed  $response
     */
    protected function isHtmlResponse($response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return strpos(strtolower($contentType), 'text/html') !== false;
    }

    /**
     * Check if request is a "Machine" request.
     */
    protected function isMachineRequest(Request $request): bool
    {
        $trigger = config('llms-txt.machine_view_trigger', 'all');

        // Check query parameter
        if (in_array($trigger, ['query', 'all'])) {
            if ($request->query('view') === 'machine' || $request->query('format') === 'markdown') {
                return true;
            }
        }

        // Check Accept header
        if (in_array($trigger, ['accept', 'all'])) {
            $accept = $request->header('Accept', '');
            if (strpos(strtolower($accept), 'text/markdown') !== false) {
                return true;
            }
        }

        // Check custom header
        if (in_array($trigger, ['header', 'all'])) {
            if ($request->header('X-View') === 'machine') {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert HTML to markdown.
     */
    public function convertHtmlToMarkdown(string $html): string
    {
        // Extract main content if selector is configured
        $selector = config('llms-txt.main_content_selector');

        if ($selector) {
            try {
                $crawler = new Crawler($html);
                $mainContent = $crawler->filter($selector);

                if ($mainContent->count() > 0) {
                    $html = $mainContent->outerHtml();
                }
            } catch (\Exception $e) {
                // If selector fails, use full HTML
            }
        }

        // Convert HTML to markdown
        $converter = new HtmlConverter([
            'strip_tags' => true,
            'use_autolinks' => true,
            'hard_break' => false,
        ]);

        return $converter->convert($html);
    }

    /**
     * Add cache headers to response.
     *
     * @param  mixed  $response
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
