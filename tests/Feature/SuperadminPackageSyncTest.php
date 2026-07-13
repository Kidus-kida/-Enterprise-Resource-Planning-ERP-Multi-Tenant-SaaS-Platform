<?php

namespace Tests\Feature;

use Modules\Superadmin\Services\ModuleManagementService;
use Tests\TestCase;

class SuperadminPackageSyncTest extends TestCase
{
    public function test_service_discovers_installed_modules_from_modules_directory(): void
    {
        $service = app(ModuleManagementService::class);

        $modules = $service->discoverInstalledModules();

        $this->assertNotEmpty($modules);
        $this->assertContains('Superadmin', $modules);
        $this->assertContains('Accounting', $modules);
    }
}
