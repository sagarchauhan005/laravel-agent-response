<?php

namespace YourVendor\LaravelLlmsTxt\Tests\Feature;

use YourVendor\LaravelLlmsTxt\Tests\TestCase;

class MachineViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register a test route
        $this->app['router']->get('/test-page', function () {
            return response('<html><body><main><h1>Test Page</h1><p>Content here</p></main></body></html>', 200, [
                'Content-Type' => 'text/html',
            ]);
        })->middleware('web');
    }

    /** @test */
    public function it_converts_html_to_markdown_with_query_parameter()
    {
        $this->app['config']->set('llms-txt.machine_view_enabled', true);
        $this->app['config']->set('llms-txt.machine_view_trigger', 'query');

        $response = $this->get('/test-page?view=machine');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertSee('Test Page', false);
    }

    /** @test */
    public function it_converts_html_to_markdown_with_accept_header()
    {
        $this->app['config']->set('llms-txt.machine_view_enabled', true);
        $this->app['config']->set('llms-txt.machine_view_trigger', 'accept');

        $response = $this->get('/test-page', [
            'Accept' => 'text/markdown',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    }

    /** @test */
    public function it_serves_normal_html_when_not_machine_request()
    {
        $this->app['config']->set('llms-txt.machine_view_enabled', true);

        $response = $this->get('/test-page');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html');
    }
}
