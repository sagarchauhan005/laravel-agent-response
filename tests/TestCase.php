<?php

namespace YourVendor\LaravelLlmsTxt\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use YourVendor\LaravelLlmsTxt\LlmsTxtServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LlmsTxtServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('llms-txt.enabled', true);
        $app['config']->set('llms-txt.title', 'Test App');
        $app['config']->set('llms-txt.description', 'Test description');
    }
}
