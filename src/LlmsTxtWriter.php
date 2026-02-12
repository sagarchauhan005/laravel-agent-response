<?php

namespace Sagarchauhan005\LaravelLlmsTxt;

class LlmsTxtWriter
{
    /**
     * Write llms.txt markdown from structured data.
     *
     * @param  string  $title
     * @param  string  $description
     * @param  string|null  $body
     * @param  array  $sections
     * @return string
     */
    public function write(string $title, string $description, ?string $body, array $sections): string
    {
        $markdown = [];

        // H1: Title (required)
        $markdown[] = '# '.$this->escapeMarkdown($title);
        $markdown[] = '';

        // Blockquote: Description (required)
        $markdown[] = '> '.$this->escapeMarkdown($description);
        $markdown[] = '';

        // Body (optional)
        if (! empty($body)) {
            $markdown[] = $body;
            $markdown[] = '';
        }

        // Sections (H2 with link lists)
        foreach ($sections as $sectionName => $links) {
            if (empty($links)) {
                continue;
            }

            // Ensure "Optional" section uses exact name per spec
            $sectionHeading = strtolower($sectionName) === 'optional' ? 'Optional' : $sectionName;
            $markdown[] = '## '.$this->escapeMarkdown($sectionHeading);
            $markdown[] = '';

            foreach ($links as $link) {
                $title = $link['title'] ?? '';
                $url = $link['url'] ?? '';
                $notes = $link['notes'] ?? null;

                if (empty($title) || empty($url)) {
                    continue;
                }

                $linkMarkdown = '- ['.$this->escapeMarkdown($title).']('.$url.')';
                
                if ($notes) {
                    $linkMarkdown .= ': '.$this->escapeMarkdown($notes);
                }

                $markdown[] = $linkMarkdown;
            }

            $markdown[] = '';
        }

        return implode("\n", $markdown);
    }

    /**
     * Escape markdown special characters in text.
     *
     * @param  string  $text
     * @return string
     */
    protected function escapeMarkdown(string $text): string
    {
        // Escape special markdown characters that could break formatting
        // But preserve links and other intentional markdown
        // For now, we'll escape brackets and backticks that aren't part of links
        // This is a simple approach - could be enhanced
        
        // Don't escape if it looks like a markdown link [text](url)
        if (preg_match('/\[.*?\]\(.*?\)/', $text)) {
            return $text;
        }

        // Escape backticks
        $text = str_replace('`', '\\`', $text);

        return $text;
    }
}
