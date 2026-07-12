<?php

namespace Modules\Superadmin\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Superadmin\Models\SystemSetting;
use Modules\Superadmin\Models\SettingsAuditLog;
use Modules\Superadmin\Facades\Setting;

class AuditLogMaskingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sensitive settings are masked in audit logs.
     * Validates: Requirements 4.3
     * 
     * @test
     */
    public function sensitive_settings_are_masked_in_audit_logs()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.password',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
            'value' => 'old-password',
        ]);
        
        // Change the password
        Setting::set('email.smtp.password', 'new-password');
        
        // Check audit log
        $log = SettingsAuditLog::where('key', 'email.smtp.password')->latest()->first();
        
        $this->assertNotNull($log);
        $this->assertEquals('••••••••', $log->old_value);
        $this->assertEquals('••••••••', $log->new_value);
        $this->assertEquals('email.smtp.password', $log->key);
    }

    /**
     * Test non-sensitive settings are not masked in audit logs.
     * Validates: Requirements 4.1, 4.2
     * 
     * @test
     */
    public function non_sensitive_settings_are_not_masked_in_audit_logs()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'company.name',
            'is_sensitive' => false,
            'is_editable' => true,
            'type' => 'string',
            'value' => 'Old Company Name',
        ]);
        
        Setting::set('company.name', 'New Company Name');
        
        $log = SettingsAuditLog::where('key', 'company.name')->latest()->first();
        
        $this->assertNotNull($log);
        $this->assertEquals('Old Company Name', $log->old_value);
        $this->assertEquals('New Company Name', $log->new_value);
    }

    /**
     * Test API keys are masked in audit logs.
     * Validates: Requirements 4.3
     * 
     * @test
     */
    public function api_keys_are_masked_in_audit_logs()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'integration.stripe.api_key',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
            'value' => '',
        ]);
        
        Setting::set('integration.stripe.api_key', 'sk_test_4eC39HqLyjWDarjtT1zdp7dc');
        
        $log = SettingsAuditLog::where('key', 'integration.stripe.api_key')->latest()->first();
        
        $this->assertNotNull($log);
        // Old value should be masked (empty string doesn't get masked)
        $this->assertEquals('', $log->old_value);
        // New value should be masked
        $this->assertEquals('••••••••', $log->new_value);
    }

    /**
     * Test storage credentials are masked in audit logs.
     * Validates: Requirements 4.3, 13.5
     * 
     * @test
     */
    public function storage_credentials_are_masked_in_audit_logs()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'storage.s3.secret',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
            'value' => 'old-secret',
        ]);
        
        Setting::set('storage.s3.secret', 'new-secret-key-xyz');
        
        $log = SettingsAuditLog::where('key', 'storage.s3.secret')->latest()->first();
        
        $this->assertNotNull($log);
        $this->assertEquals('••••••••', $log->old_value);
        $this->assertEquals('••••••••', $log->new_value);
        $this->assertNotEquals('new-secret-key-xyz', $log->new_value);
    }

    /**
     * Test audit log records setting key even for sensitive settings.
     * Validates: Requirements 4.3
     * 
     * @test
     */
    public function audit_log_records_key_for_sensitive_settings()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'notification.slack.webhook',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        Setting::set('notification.slack.webhook', 'https://hooks.slack.com/services/XXX');
        
        $log = SettingsAuditLog::where('key', 'notification.slack.webhook')->latest()->first();
        
        $this->assertNotNull($log);
        $this->assertEquals('notification.slack.webhook', $log->key);
        $this->assertNotNull($log->user_id);
        $this->assertNotNull($log->changed_at);
    }

    /**
     * Test empty sensitive values are not masked (remain empty).
     * Validates: Requirements 4.3
     * 
     * @test
     */
    public function empty_sensitive_values_are_not_masked()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.password',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
            'value' => '',
        ]);
        
        Setting::set('email.smtp.password', '');
        
        $log = SettingsAuditLog::where('key', 'email.smtp.password')->latest()->first();
        
        $this->assertNotNull($log);
        // Empty values should remain empty, not masked
        $this->assertEquals('', $log->old_value);
        $this->assertEquals('', $log->new_value);
    }

    /**
     * Test audit log contains user and timestamp information.
     * Validates: Requirements 4.1, 4.2
     * 
     * @test
     */
    public function audit_log_contains_user_and_timestamp_information()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        
        $setting = SystemSetting::factory()->create([
            'key' => 'general.timezone',
            'is_sensitive' => false,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        Setting::set('general.timezone', 'America/New_York');
        
        $log = SettingsAuditLog::where('key', 'general.timezone')->latest()->first();
        
        $this->assertNotNull($log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
        $this->assertNotNull($log->browser);
        $this->assertNotNull($log->device);
        $this->assertNotNull($log->changed_at);
    }

    /**
     * Test multiple sensitive settings are all masked.
     * Validates: Requirements 4.3
     * 
     * @test
     */
    public function multiple_sensitive_settings_are_all_masked()
    {
        $settings = [
            'email.smtp.password' => 'email-pass',
            'storage.s3.secret' => 's3-secret',
            'integration.stripe.api_key' => 'stripe-key',
        ];
        
        foreach ($settings as $key => $value) {
            SystemSetting::factory()->create([
                'key' => $key,
                'is_sensitive' => true,
                'is_editable' => true,
                'type' => 'string',
            ]);
            
            Setting::set($key, $value);
        }
        
        foreach ($settings as $key => $value) {
            $log = SettingsAuditLog::where('key', $key)->latest()->first();
            
            $this->assertNotNull($log, "Audit log should exist for {$key}");
            $this->assertEquals('••••••••', $log->new_value, "Value should be masked for {$key}");
            $this->assertNotEquals($value, $log->new_value, "Original value should not be in audit log for {$key}");
        }
    }
}
