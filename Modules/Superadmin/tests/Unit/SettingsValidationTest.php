<?php

namespace Modules\Superadmin\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Superadmin\Models\SystemSetting;
use Modules\Superadmin\Facades\Setting;

class SettingsValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test invalid email format fails validation.
     * Validates: Requirements 3.1, 3.4, 3.5, 7.5
     * 
     * @test
     */
    public function invalid_email_format_fails_validation()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'company.contact.email',
            'validation_rules' => 'required|email',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed');
        
        Setting::set('company.contact.email', 'not-an-email');
    }

    /**
     * Test valid email passes validation.
     * Validates: Requirements 3.1
     * 
     * @test
     */
    public function valid_email_passes_validation()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'company.contact.email',
            'validation_rules' => 'required|email',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $result = Setting::set('company.contact.email', 'contact@example.com');
        
        $this->assertInstanceOf(SystemSetting::class, $result);
        $this->assertEquals('contact@example.com', Setting::get('company.contact.email'));
    }

    /**
     * Test required field validation.
     * Validates: Requirements 3.1, 3.4
     * 
     * @test
     */
    public function required_field_validation_fails_for_empty_value()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'general.system.name',
            'validation_rules' => 'required',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        
        Setting::set('general.system.name', '');
    }

    /**
     * Test numeric validation.
     * Validates: Requirements 3.1, 3.5
     * 
     * @test
     */
    public function numeric_validation_rejects_non_numeric_values()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.port',
            'validation_rules' => 'required|numeric|min:1|max:65535',
            'is_editable' => true,
            'type' => 'integer',
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        
        Setting::set('email.smtp.port', 'not-a-number');
    }

    /**
     * Test numeric range validation.
     * Validates: Requirements 3.1
     * 
     * @test
     */
    public function numeric_range_validation_enforces_limits()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.port',
            'validation_rules' => 'required|numeric|min:1|max:65535',
            'is_editable' => true,
            'type' => 'integer',
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        
        Setting::set('email.smtp.port', 99999);
    }

    /**
     * Test valid numeric value passes.
     * Validates: Requirements 3.1
     * 
     * @test
     */
    public function valid_numeric_value_passes_validation()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.port',
            'validation_rules' => 'required|numeric|min:1|max:65535',
            'is_editable' => true,
            'type' => 'integer',
        ]);
        
        $result = Setting::set('email.smtp.port', 587);
        
        $this->assertEquals(587, Setting::get('email.smtp.port'));
    }

    /**
     * Test URL validation.
     * Validates: Requirements 7.5
     * 
     * @test
     */
    public function url_validation_rejects_invalid_urls()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'company.website',
            'validation_rules' => 'nullable|url',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        
        Setting::set('company.website', 'not-a-url');
    }

    /**
     * Test URL validation passes for valid URLs.
     * Validates: Requirements 7.5
     * 
     * @test
     */
    public function valid_url_passes_validation()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'company.website',
            'validation_rules' => 'nullable|url',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $result = Setting::set('company.website', 'https://example.com');
        
        $this->assertEquals('https://example.com', Setting::get('company.website'));
    }

    /**
     * Test hex color validation.
     * Validates: Requirements 3.1
     * 
     * @test
     */
    public function hex_color_validation_rejects_invalid_colors()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'appearance.colors.primary',
            'validation_rules' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $this->expectException(\InvalidArgumentException::class);
        
        Setting::set('appearance.colors.primary', 'blue');
    }

    /**
     * Test valid hex color passes.
     * Validates: Requirements 3.1
     * 
     * @test
     */
    public function valid_hex_color_passes_validation()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'appearance.colors.primary',
            'validation_rules' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $result = Setting::set('appearance.colors.primary', '#4e73df');
        
        $this->assertEquals('#4e73df', Setting::get('appearance.colors.primary'));
    }

    /**
     * Test setMany validates all settings before saving any.
     * Validates: Requirements 3.4
     * 
     * @test
     */
    public function setMany_validates_all_before_saving()
    {
        $setting1 = SystemSetting::factory()->create([
            'key' => 'company.email',
            'validation_rules' => 'required|email',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $setting2 = SystemSetting::factory()->create([
            'key' => 'company.name',
            'validation_rules' => 'required|min:3',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        try {
            Setting::setMany([
                'company.email' => 'invalid-email',
                'company.name' => 'AB', // Too short
            ]);
            
            $this->fail('Expected ValidationException to be thrown');
        } catch (ValidationException $e) {
            // Both validation errors should be present
            $this->assertArrayHasKey('company.email', $e->errors());
            $this->assertArrayHasKey('company.name', $e->errors());
        }
        
        // Verify neither setting was saved
        $this->assertNull(Setting::get('company.email'));
        $this->assertNull(Setting::get('company.name'));
    }

    /**
     * Test settings without validation rules are accepted.
     * Validates: Requirements 3.1
     * 
     * @test
     */
    public function settings_without_validation_rules_accept_any_value()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'general.description',
            'validation_rules' => null,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $result = Setting::set('general.description', 'Any value works here!');
        
        $this->assertEquals('Any value works here!', Setting::get('general.description'));
    }
}
