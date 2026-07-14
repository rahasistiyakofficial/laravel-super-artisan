<?php

namespace RahasIstiyak\SuperArtisan\Tests\Feature;

use RahasIstiyak\SuperArtisan\Tests\TestCase;

class RunWorkflowCommandTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        // Define test workflows in config
        $app['config']->set('super-artisan.workflows', [
            'test-workflow' => [
                'description' => 'A simple test workflow.',
                'commands'    => [
                    'config:clear',
                    'view:clear',
                ],
            ],
            'failing-workflow' => [
                'description' => 'A workflow with a step that always fails.',
                'commands'    => [
                    'config:clear',
                    'this:command:does-not-exist',
                    'view:clear',
                ],
            ],
        ]);
    }

    public function test_run_workflow_executes_commands(): void
    {
        $this->artisan('run:workflow', ['workflow' => 'test-workflow'])
            ->assertExitCode(0);
    }

    public function test_run_workflow_dry_run_skips_execution(): void
    {
        // In dry-run mode the command should succeed without actually running steps
        $this->artisan('run:workflow', [
            'workflow'  => 'test-workflow',
            '--dry-run' => true,
        ])->assertExitCode(0);
    }

    public function test_run_workflow_shows_error_for_unknown_workflow(): void
    {
        $this->artisan('run:workflow', ['workflow' => 'nonexistent-workflow'])
            ->assertExitCode(1);
    }

    public function test_run_workflow_list_flag(): void
    {
        $this->artisan('run:workflow', [
            'workflow' => 'test-workflow',
            '--list'   => true,
        ])->assertExitCode(0);
    }

    public function test_super_list_command(): void
    {
        $this->artisan('super:list')
            ->assertExitCode(0);
    }

    public function test_super_list_with_blueprints_flag(): void
    {
        $this->artisan('super:list', ['--blueprints' => true])
            ->assertExitCode(0);
    }

    public function test_super_list_with_all_flag(): void
    {
        $this->artisan('super:list', ['--all' => true])
            ->assertExitCode(0);
    }
}
