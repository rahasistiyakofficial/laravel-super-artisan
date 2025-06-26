<?php

namespace RahasIstiyak\SuperArtisan\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MakeSuperCommandTest extends TestCase
{
    public function test_make_super_command_creates_files()
    {
        Artisan::call('make:super Post --model_path=Custom');

        $this->assertFileExists(app_path('Models/Custom/Post.php'));
        $this->assertFileExists(app_path('Http/Controllers/PostController.php'));
        $this->assertFileExists(resource_path('views/post/index.blade.php'));
    }

    public function test_make_super_with_multiple_frontend_options_fails()
    {
        $exitCode = Artisan::call('make:super Post --livewire --vue');
        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Only one of --livewire, --vue, or --react can be used.', Artisan::output());
    }
}