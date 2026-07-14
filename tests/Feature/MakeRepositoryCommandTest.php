<?php

namespace RahasIstiyak\SuperArtisan\Tests\Feature;

use RahasIstiyak\SuperArtisan\Tests\TestCase;

class MakeRepositoryCommandTest extends TestCase
{
    public function test_make_repository_creates_repository_class(): void
    {
        $this->artisan('make:repository', [
            'name'    => 'PostRepository',
            '--model' => 'Post',
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Repositories/PostRepository.php'));
        $content = file_get_contents(app_path('Repositories/PostRepository.php'));
        $this->assertStringContainsString('class PostRepository', $content);
        $this->assertStringContainsString('PostRepository', $content);
    }

    public function test_make_repository_creates_interface(): void
    {
        $this->artisan('make:repository', [
            'name'    => 'PostRepository',
            '--model' => 'Post',
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Repositories/Contracts/PostRepositoryInterface.php'));
        $content = file_get_contents(app_path('Repositories/Contracts/PostRepositoryInterface.php'));
        $this->assertStringContainsString('interface PostRepositoryInterface', $content);
    }

    public function test_make_repository_creates_service_provider(): void
    {
        $this->artisan('make:repository', [
            'name'    => 'PostRepository',
            '--model' => 'Post',
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Providers/RepositoryServiceProvider.php'));
        $content = file_get_contents(app_path('Providers/RepositoryServiceProvider.php'));
        $this->assertStringContainsString('PostRepository', $content);
    }

    public function test_make_repository_skips_interface_with_flag(): void
    {
        $this->artisan('make:repository', [
            'name'           => 'PostRepository',
            '--model'        => 'Post',
            '--no-interface' => true,
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Repositories/PostRepository.php'));
        $this->assertFileDoesNotExist(app_path('Repositories/Contracts/PostRepositoryInterface.php'));
    }

    public function test_make_repository_skips_binding_with_flag(): void
    {
        $this->artisan('make:repository', [
            'name'         => 'PostRepository',
            '--model'      => 'Post',
            '--no-binding' => true,
        ])->assertExitCode(0);

        $this->assertFileDoesNotExist(app_path('Providers/RepositoryServiceProvider.php'));
    }

    public function test_make_repository_dry_run(): void
    {
        $this->artisan('make:repository', [
            'name'      => 'PostRepository',
            '--model'   => 'Post',
            '--dry-run' => true,
        ])->assertExitCode(0);

        $this->assertFileDoesNotExist(app_path('Repositories/PostRepository.php'));
        $this->assertFileDoesNotExist(app_path('Repositories/Contracts/PostRepositoryInterface.php'));
    }

    public function test_make_repository_infers_model_from_name(): void
    {
        $this->artisan('make:repository', [
            'name' => 'ArticleRepository',
            // no --model option; should infer 'Article'
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Repositories/ArticleRepository.php'));
        $content = file_get_contents(app_path('Repositories/ArticleRepository.php'));
        $this->assertStringContainsString('Article', $content);
    }
}
