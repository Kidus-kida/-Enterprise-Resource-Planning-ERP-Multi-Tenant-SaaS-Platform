<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandingHelperTest extends TestCase
{
    public function test_brand_helper_uses_default_company_name_when_no_setting_exists(): void
    {
        $this->assertSame('MD Code Inc.', brand('name'));
    }

    public function test_brand_helper_returns_public_asset_fallbacks_when_settings_are_empty(): void
    {
        $this->assertSame(asset('images/main-logo.png'), brand('logo'));
        $this->assertSame(asset('images/main-logo.png'), brand('dark_logo'));
        $this->assertSame(asset('favicon.ico'), brand('favicon'));
        $this->assertSame(asset('images/main-logo.png'), brand('login_logo'));
    }
}
