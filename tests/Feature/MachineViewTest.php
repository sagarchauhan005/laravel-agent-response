<?php

namespace Sagarchauhan005\LaravelLlmsTxt\Tests\Feature;

use Sagarchauhan005\LaravelLlmsTxt\Middleware\ServeMachineView;
use Sagarchauhan005\LaravelLlmsTxt\Tests\TestCase;

class MachineViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure machine view is enabled before registering routes
        $this->app['config']->set('llms-txt.machine_view_enabled', true);

        // Register a test route with middleware applied directly
        $this->app['router']->get('/test-page', function () {
            return response('<html><body><main><h1>Test Page</h1><p>Content here</p></main></body></html>', 200, [
                'Content-Type' => 'text/html',
            ]);
        })->middleware([ServeMachineView::class]);
    }

    /** @test */
    public function it_converts_html_to_markdown_with_query_parameter()
    {
        // Ensure config is set before making request
        $this->app['config']->set('llms-txt.machine_view_enabled', true);
        $this->app['config']->set('llms-txt.machine_view_trigger', 'query');

        $response = $this->get('/test-page?view=machine');

        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $this->assertNotNull($contentType);
        $this->assertStringContainsString('text/markdown', $contentType);
        $response->assertSee('Test Page', false);
    }

    /** @test */
    public function it_converts_html_to_markdown_with_accept_header()
    {
        // Ensure config is set before making request
        $this->app['config']->set('llms-txt.machine_view_enabled', true);
        $this->app['config']->set('llms-txt.machine_view_trigger', 'accept');

        $response = $this->get('/test-page', [
            'Accept' => 'text/markdown',
        ]);

        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $this->assertNotNull($contentType);
        $this->assertStringContainsString('text/markdown', $contentType);
    }

    /** @test */
    public function it_serves_normal_html_when_not_machine_request()
    {
        $this->app['config']->set('llms-txt.machine_view_enabled', true);

        $response = $this->get('/test-page');

        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $this->assertNotNull($contentType);
        $this->assertStringContainsString('text/html', $contentType);
    }
}
