<?php

namespace Sagarchauhan005\LaravelLlmsTxt;

class LlmsTxtService
{
    /**
     * Create a new LlmsTxtService instance.
     */
    public function __construct(
        protected LlmsTxtWriter $writer
    ) {
    }

    /**
     * Generate llms.txt markdown content.
     *
     * @return string
     */
    public function generate(): string
    {
        $config = $this->getMergedConfig();

        $title = $config['title'] ?? config('app.name', 'Laravel Application');
        $description = $config['description'] ?? '';
        $body = $config['body'] ?? null;
        $sections = $config['sections'] ?? [];

        return $this->writer->write($title, $description, $body, $sections);
    }

    /**
     * Get merged config with preset applied.
     *
     * @return array
     */
    protected function getMergedConfig(): array
    {
        $useCase = config('llms-txt.use_case', 'custom');
        $presets = config('llms-txt.presets', []);
        $preset = $presets[$useCase] ?? [];

        $config = [
            'title' => config('llms-txt.title'),
            'description' => config('llms-txt.description'),
            'body' => config('llms-txt.body'),
            'sections' => config('llms-txt.sections', []),
        ];

        // Merge preset (preset values override main config, except null values)
        if (! empty($preset)) {
            if (isset($preset['title']) && $preset['title'] !== null) {
                $config['title'] = $preset['title'];
            }

            if (isset($preset['description']) && $preset['description'] !== null) {
                $config['description'] = $preset['description'];
            }

            if (isset($preset['body']) && $preset['body'] !== null) {
                $config['body'] = $preset['body'];
            }

            // Merge sections (preset sections are merged with main config sections)
            if (isset($preset['sections']) && is_array($preset['sections'])) {
                $config['sections'] = array_merge($preset['sections'], $config['sections']);
            }
        }

        return $config;
    }
}
