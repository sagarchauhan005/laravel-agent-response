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
     *
     * This method is defensive:
     * - It narrows to the configured main content selector when possible
     * - It strips clearly non-content tags (script/style/iframes, etc.)
     * - It trims excessively large HTML payloads to a configurable size
     * - It always returns markdown, even on errors, with a fallback message
     *   that tells agents how to fetch the original HTML.
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
            } catch (\Throwable $e) {
                // If selector fails, fall back to full HTML
            }
        }

        // Strip obviously non-content tags to reduce noise and size
        // (scripts, styles, templates, and embedded media)
        $html = preg_replace(
            '#<(script|style|noscript|template|iframe|svg|canvas|video|audio|source|track)\b[^>]*>.*?</\1>#is',
            '',
            $html
        ) ?? $html;

        // Guard against extremely large HTML responses
        $maxLength = (int) config('llms-txt.machine_view_max_html_length', 500000);
        $wasTruncated = false;

        if ($maxLength > 0 && strlen($html) > $maxLength) {
            $html = substr($html, 0, $maxLength);
            $wasTruncated = true;
        }

        // Convert HTML to markdown, but never throw an uncaught error to the caller
        try {
            $converter = new HtmlConverter([
                'strip_tags' => true,
                'use_autolinks' => true,
                'hard_break' => false,
            ]);

            $markdown = $converter->convert($html);
        } catch (\Throwable $e) {
            // If conversion fails entirely, return a small, safe markdown stub
            return $this->fallbackMarkdownMessage();
        }

        // Clean whitespace to reduce token waste
        $markdown = $this->cleanMarkdownWhitespace($markdown);

        if ($wasTruncated) {
            $notice = <<<MD
> NOTE: This machine view was generated from a **truncated** version of the original HTML because the page was too large to convert in full.  
> If you need complete detail, fetch the original HTML by requesting the same URL **without** the `.md` extension or `view=machine`/`format=markdown` query parameters.


MD;

            return $notice.$markdown;
        }

        return $markdown;
    }

    /**
     * Fallback machine-view message when markdown conversion fails completely.
     */
    protected function fallbackMarkdownMessage(): string
    {
        return <<<MD
# Machine view unavailable

> This page was too large or complex to convert to markdown safely.

You can still access all of the content by requesting the same URL in its **normal HTML** form (for example, without the `.md` extension or the `view=machine` / `format=markdown` query parameters).

MD;
    }

    /**
     * Clean excessive whitespace from markdown to reduce token waste.
     *
     * This method:
     * - Removes excessive blank lines (max 2 consecutive)
     * - Merges split markdown links/images (e.g., `[]` and `(url)` on separate lines)
     * - Removes excessive indentation (beyond 4 spaces, except for lists)
     * - Collapses multiple spaces to single space (preserves URLs and code blocks)
     *
     * @param  string  $markdown
     * @return string
     */
    protected function cleanMarkdownWhitespace(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $cleaned = [];
        $inCodeBlock = false;
        $blankLineCount = 0;

        foreach ($lines as $line) {
            // Track code blocks (```)
            if (preg_match('/^```/', $line)) {
                $inCodeBlock = !$inCodeBlock;
                $cleaned[] = $line;
                $blankLineCount = 0;
                continue;
            }

            // Preserve code blocks as-is
            if ($inCodeBlock) {
                $cleaned[] = $line;
                continue;
            }

            // Trim trailing whitespace
            $line = rtrim($line);

            // Skip excessive blank lines (max 2 consecutive)
            if ($line === '') {
                $blankLineCount++;
                if ($blankLineCount <= 2) {
                    $cleaned[] = '';
                }
                continue;
            }

            $blankLineCount = 0;

            // Fix markdown links/images split across lines
            // Pattern: `[]` on one line, `(url)` on next line with indentation
            if (preg_match('/^\s*\[\]\s*$/', $line) && !empty($cleaned)) {
                // Check if previous line ends with `![]` or `[]`
                $prevIndex = count($cleaned) - 1;
                while ($prevIndex >= 0 && trim($cleaned[$prevIndex]) === '') {
                    $prevIndex--;
                }
                if ($prevIndex >= 0 && preg_match('/!?\[\]\s*$/', rtrim($cleaned[$prevIndex]))) {
                    // Skip adding this standalone `[]` line - it's redundant
                    continue;
                }
            }

            // Merge link text `[]` with URL `(url)` if they're on separate lines
            if (preg_match('/^\s*\(https?:\/\/[^)]+\)\s*$/', $line) && !empty($cleaned)) {
                $prevIndex = count($cleaned) - 1;
                while ($prevIndex >= 0 && trim($cleaned[$prevIndex]) === '') {
                    $prevIndex--;
                }
                if ($prevIndex >= 0 && preg_match('/!?\[\]\s*$/', rtrim($cleaned[$prevIndex]))) {
                    // Merge: replace previous line's `[]` with `[]` + URL
                    $cleaned[$prevIndex] = rtrim($cleaned[$prevIndex]) . ' ' . trim($line);
                    continue;
                }
            }

            // Remove excessive indentation (more than 4 spaces, except for lists)
            if (preg_match('/^\s{5,}/', $line) && !preg_match('/^(\s*)[-*+]\s/', $line)) {
                $line = preg_replace('/^\s{5,}/', '    ', $line);
            }

            // Collapse multiple spaces to single space (except in URLs)
            $line = preg_replace('/(?<!https?:)\s{2,}(?!\/\/)/', ' ', $line);

            $cleaned[] = $line;
        }

        $result = implode("\n", $cleaned);

        // Final cleanup: remove more than 2 consecutive blank lines
        $result = preg_replace('/\n{3,}/', "\n\n", $result);

        return trim($result);
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
