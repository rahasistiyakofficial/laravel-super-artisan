<?php

namespace RahasIstiyak\SuperArtisan\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RahasIstiyak\SuperArtisan\Providers\SuperArtisanServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SuperArtisanServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Use in-memory SQLite for any DB interactions
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Clean up generated files after each test.
     */
    protected function tearDown(): void
    {
        $this->cleanDirectory(app_path('Models'));
        $this->cleanDirectory(app_path('Http/Controllers'));
        $this->cleanDirectory(app_path('Repositories'));
        $this->cleanDirectory(app_path('Services'));
        $this->cleanDirectory(app_path('Policies'));
        $this->cleanDirectory(app_path('Actions'));
        $this->cleanDirectory(app_path('Http/Requests'));
        $this->cleanDirectory(resource_path('views'));

        parent::tearDown();
    }

    protected function cleanDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }

        rmdir($dir);
    }
}
