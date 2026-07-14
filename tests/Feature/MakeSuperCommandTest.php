<?php

namespace RahasIstiyak\SuperArtisan\Tests\Feature;

use RahasIstiyak\SuperArtisan\Tests\TestCase;

class MakeSuperCommandTest extends TestCase
{
    public function test_make_super_generates_blade_views(): void
    {
        $this->artisan('make:super', ['name' => 'Post'])
            ->assertExitCode(0);

        $this->assertFileExists(resource_path('views/post/index.blade.php'));
        $this->assertFileExists(resource_path('views/post/create.blade.php'));
        $this->assertFileExists(resource_path('views/post/edit.blade.php'));
        $this->assertFileExists(resource_path('views/post/show.blade.php'));
    }

    public function test_make_super_with_custom_view_path(): void
    {
        $this->artisan('make:super', [
            'name'        => 'Post',
            '--view_path' => 'admin/posts',
        ])->assertExitCode(0);

        $this->assertFileExists(resource_path('views/admin/posts/index.blade.php'));
    }

    public function test_make_super_dry_run_creates_no_files(): void
    {
        $this->artisan('make:super', [
            'name'      => 'Product',
            '--dry-run' => true,
        ])->assertExitCode(0);

        $this->assertFileDoesNotExist(resource_path('views/product/index.blade.php'));
    }

    public function test_make_super_force_overwrites_existing_files(): void
    {
        // Generate once
        $this->artisan('make:super', ['name' => 'Tag'])->assertExitCode(0);

        // Modify the file
        $viewPath = resource_path('views/tag/index.blade.php');
        file_put_contents($viewPath, '<!-- modified -->');

        // Force overwrite
        $this->artisan('make:super', [
            'name'    => 'Tag',
            '--force' => true,
        ])->assertExitCode(0);

        // Should be restored to stub content (not our modification)
        $this->assertStringNotContainsString('<!-- modified -->', file_get_contents($viewPath));
    }

    public function test_make_super_bulk_generation(): void
    {
        $this->artisan('make:super', ['name' => 'Post,Comment'])
            ->assertExitCode(0);

        $this->assertFileExists(resource_path('views/post/index.blade.php'));
        $this->assertFileExists(resource_path('views/comment/index.blade.php'));
    }

    public function test_make_super_rejects_multiple_frontend_options(): void
    {
        $this->artisan('make:super', [
            'name'      => 'Post',
            '--livewire' => true,
            '--vue'     => true,
        ])->assertExitCode(1);
    }

    public function test_make_super_with_api_flag(): void
    {
        $this->artisan('make:super', [
            'name'  => 'Article',
            '--api' => true,
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Http/Controllers/ArticleController.php'));
        // API should NOT generate blade views
        $this->assertFileDoesNotExist(resource_path('views/article/index.blade.php'));
    }

    public function test_make_super_with_domain_option(): void
    {
        $this->artisan('make:super', [
            'name'     => 'Post',
            '--domain' => 'Blog',
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('Http/Controllers/Blog/PostController.php'));
    }

    public function test_make_super_generates_vue_component(): void
    {
        $this->artisan('make:super', [
            'name'  => 'Post',
            '--vue' => true,
        ])->assertExitCode(0);

        $this->assertFileExists(resource_path('js/components/Post.vue'));
    }

    public function test_make_super_generates_react_component(): void
    {
        $this->artisan('make:super', [
            'name'    => 'Post',
            '--react' => true,
        ])->assertExitCode(0);

        $this->assertFileExists(resource_path('js/components/Post.jsx'));
    }
}