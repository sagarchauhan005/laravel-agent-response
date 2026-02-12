<?php

namespace YourVendor\LaravelLlmsTxt\Tests\Unit;

use YourVendor\LaravelLlmsTxt\LlmsTxtWriter;
use YourVendor\LaravelLlmsTxt\Tests\TestCase;

class LlmsTxtWriterTest extends TestCase
{
    protected LlmsTxtWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->writer = new LlmsTxtWriter();
    }

    /** @test */
    public function it_writes_basic_llms_txt_format()
    {
        $markdown = $this->writer->write(
            'Test App',
            'Test description',
            null,
            []
        );

        $this->assertStringContainsString('# Test App', $markdown);
        $this->assertStringContainsString('> Test description', $markdown);
    }

    /** @test */
    public function it_includes_body_when_provided()
    {
        $markdown = $this->writer->write(
            'Test App',
            'Test description',
            'Some body content here',
            []
        );

        $this->assertStringContainsString('Some body content here', $markdown);
    }

    /** @test */
    public function it_includes_sections_with_links()
    {
        $sections = [
            'Docs' => [
                ['title' => 'Getting Started', 'url' => '/docs/start', 'notes' => 'Quick guide'],
            ],
        ];

        $markdown = $this->writer->write(
            'Test App',
            'Test description',
            null,
            $sections
        );

        $this->assertStringContainsString('## Docs', $markdown);
        $this->assertStringContainsString('[Getting Started](/docs/start)', $markdown);
        $this->assertStringContainsString(': Quick guide', $markdown);
    }

    /** @test */
    public function it_handles_optional_section_specially()
    {
        $sections = [
            'optional' => [
                ['title' => 'Extra', 'url' => '/extra'],
            ],
        ];

        $markdown = $this->writer->write(
            'Test App',
            'Test description',
            null,
            $sections
        );

        $this->assertStringContainsString('## Optional', $markdown);
    }
}
