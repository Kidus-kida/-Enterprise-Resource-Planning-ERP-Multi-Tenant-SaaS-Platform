<?php

namespace Modules\Superadmin\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Models\SystemSetting;
use Modules\Superadmin\Facades\Setting;

class SettingsEncryptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sensitive setting password is encrypted in database.
     * Validates: Requirements 1.3, 1.4
     * 
     * @test
     */
    public function sensitive_setting_password_is_encrypted_in_database()
    {
        // Create a sensitive setting
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.password',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Set the password
        Setting::set('email.smtp.password', 'secret123');
        
        // Get raw value from database
        $raw = DB::table('system_settings')
            ->where('key', 'email.smtp.password')
            ->value('value');
        
        // Verify it's encrypted (not plain text)
        $this->assertNotEquals('secret123', $raw);
        $this->assertNotEmpty($raw);
        
        // Verify we can decrypt it
        $decrypted = Crypt::decryptString($raw);
        $this->assertEquals('secret123', $decrypted);
    }

    /**
     * Test sensitive setting can be retrieved decrypted.
     * Validates: Requirements 1.4
     * 
     * @test
     */
    public function sensitive_setting_retrieves_decrypted_value()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'storage.s3.secret',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Set a sensitive value
        $secretValue = 'my-secret-key-12345';
        Setting::set('storage.s3.secret', $secretValue);
        
        // Retrieve it
        $retrieved = Setting::get('storage.s3.secret');
        
        // Should get the original value back
        $this->assertEquals($secretValue, $retrieved);
    }

    /**
     * Test encryption round-trip for API keys.
     * Validates: Requirements 1.3, 1.4, 11.2, 13.5
     * 
     * @test
     */
    public function api_key_encryption_round_trip()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'integration.stripe.api_key',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $apiKey = 'sk_test_4eC39HqLyjWDarjtT1zdp7dc';
        
        // Save the API key
        Setting::set('integration.stripe.api_key', $apiKey);
        
        // Verify raw database storage is encrypted
        $rawValue = DB::table('system_settings')
            ->where('key', 'integration.stripe.api_key')
            ->value('value');
            
        $this->assertNotEquals($apiKey, $rawValue);
        
        // Verify retrieval gives original value
        $retrieved = Setting::get('integration.stripe.api_key');
        $this->assertEquals($apiKey, $retrieved);
    }

    /**
     * Test empty sensitive values are not encrypted.
     * Validates: Requirements 1.3
     * 
     * @test
     */
    public function empty_sensitive_values_are_not_encrypted()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'email.smtp.password',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
            'value' => '',
        ]);
        
        Setting::set('email.smtp.password', '');
        
        $raw = DB::table('system_settings')
            ->where('key', 'email.smtp.password')
            ->value('value');
        
        // Empty values should remain empty, not encrypted
        $this->assertEquals('', $raw);
    }

    /**
     * Test non-sensitive settings are not encrypted.
     * Validates: Requirements 1.3
     * 
     * @test
     */
    public function non_sensitive_settings_are_not_encrypted()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'company.name',
            'is_sensitive' => false,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        Setting::set('company.name', 'Acme Corp');
        
        $raw = DB::table('system_settings')
            ->where('key', 'company.name')
            ->value('value');
        
        // Non-sensitive values should be stored as plain text
        $this->assertEquals('Acme Corp', $raw);
    }

    /**
     * Test typed_value accessor decrypts sensitive settings.
     * Validates: Requirements 1.4
     * 
     * @test
     */
    public function typed_value_accessor_decrypts_sensitive_settings()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'notification.slack.webhook',
            'is_sensitive' => true,
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $webhookUrl = 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXX';
        
        Setting::set('notification.slack.webhook', $webhookUrl);
        
        // Retrieve through model
        $setting->refresh();
        
        // typed_value should decrypt automatically
        $this->assertEquals($webhookUrl, $setting->typed_value);
    }
}
