<?php

namespace Sagarchauhan005\LaravelLlmsTxt\Tests\Feature;

use Sagarchauhan005\LaravelLlmsTxt\Tests\TestCase;

class LlmsTxtRouteTest extends TestCase
{
    /** @test */
    public function it_serves_llms_txt_endpoint()
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertSee('# Test App', false);
        $response->assertSee('> Test description', false);
    }

    /** @test */
    public function it_includes_cache_headers_when_enabled()
    {
        $this->app['config']->set('llms-txt.cache_enabled', true);
        $this->app['config']->set('llms-txt.cache_max_age', 3600);
        $this->app['config']->set('llms-txt.cache_visibility', 'public');

        $response = $this->get('/llms.txt');

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertNotNull($cacheControl);
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
    }

    /** @test */
    public function it_respects_enabled_config()
    {
        $this->app['config']->set('llms-txt.enabled', false);

        $response = $this->get('/llms.txt');

        $response->assertStatus(404);
    }
}
