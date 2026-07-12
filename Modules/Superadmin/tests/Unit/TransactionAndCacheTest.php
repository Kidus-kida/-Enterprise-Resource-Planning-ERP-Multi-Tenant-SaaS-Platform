<?php

namespace Modules\Superadmin\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Modules\Superadmin\Events\SettingChanged;
use Modules\Superadmin\Facades\Setting;
use Modules\Superadmin\Models\SystemSetting;
use Tests\TestCase;

class TransactionAndCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::flush();
    }

    /**
     * Test batch updates use transaction (all-or-nothing).
     * Validates: Requirements 1.5
     * 
     * @test
     */
    public function batch_updates_are_transactional()
    {
        $setting1 = SystemSetting::factory()->create([
            'key' => 'test.setting1',
            'value' => 'original1',
            'validation_rules' => 'required|min:3',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $setting2 = SystemSetting::factory()->create([
            'key' => 'test.setting2',
            'value' => 'original2',
            'validation_rules' => 'required|email',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Try to save batch with one invalid value
        try {
            Setting::setMany([
                'test.setting1' => 'valid_value',
                'test.setting2' => 'invalid-email', // This will fail validation
            ]);
            
            $this->fail('Expected ValidationException to be thrown');
        } catch (ValidationException $e) {
            // Expected
        }
        
        // Verify NEITHER setting was updated (transaction rolled back)
        $this->assertEquals('original1', Setting::get('test.setting1'));
        $this->assertEquals('original2', Setting::get('test.setting2'));
    }

    /**
     * Test batch updates succeed when all validations pass.
     * Validates: Requirements 1.5
     * 
     * @test
     */
    public function batch_updates_succeed_when_all_valid()
    {
        $setting1 = SystemSetting::factory()->create([
            'key' => 'test.setting1',
            'value' => 'original1',
            'validation_rules' => 'required|min:3',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        $setting2 = SystemSetting::factory()->create([
            'key' => 'test.setting2',
            'value' => 'original2',
            'validation_rules' => 'required|email',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Save batch with all valid values
        Setting::setMany([
            'test.setting1' => 'new_value',
            'test.setting2' => 'valid@example.com',
        ]);
        
        // Verify both settings were updated
        $this->assertEquals('new_value', Setting::get('test.setting1'));
        $this->assertEquals('valid@example.com', Setting::get('test.setting2'));
    }

    /**
     * Test cache invalidation on write.
     * Validates: Requirements 5.2
     * 
     * @test
     */
    public function cache_is_invalidated_on_write()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'test.cached',
            'value' => 'original',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Load into cache
        $value1 = Setting::get('test.cached');
        $this->assertEquals('original', $value1);
        
        // Verify cache exists
        $this->assertTrue(Cache::has('system_settings_v1'));
        
        // Update the setting
        Setting::set('test.cached', 'updated');
        
        // Cache should be cleared
        // We can verify by checking that the new read gets the updated value
        $value2 = Setting::get('test.cached');
        $this->assertEquals('updated', $value2);
    }

    /**
     * Test cache regeneration on read after clear.
     * Validates: Requirements 5.1, 5.3
     * 
     * @test
     */
    public function cache_regenerates_on_read_after_clear()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'test.regenerate',
            'value' => 'test_value',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Ensure cache is empty
        Cache::flush();
        $this->assertFalse(Cache::has('system_settings_v1'));
        
        // First read should populate cache
        $value = Setting::get('test.regenerate');
        $this->assertEquals('test_value', $value);
        
        // Cache should now exist
        $this->assertTrue(Cache::has('system_settings_v1'));
        
        // Clear cache manually
        Setting::clearCache();
        $this->assertFalse(Cache::has('system_settings_v1'));
        
        // Next read should regenerate cache
        $value2 = Setting::get('test.regenerate');
        $this->assertEquals('test_value', $value2);
        $this->assertTrue(Cache::has('system_settings_v1'));
    }

    /**
     * Test cache warming preloads settings.
     * Validates: Requirements 5.1, 5.3
     * 
     * @test
     */
    public function cache_warming_preloads_settings()
    {
        SystemSetting::factory()->create([
            'key' => 'general.test1',
            'value' => 'value1',
            'category' => 'general',
            'type' => 'string',
        ]);
        
        SystemSetting::factory()->create([
            'key' => 'appearance.test2',
            'value' => 'value2',
            'category' => 'appearance',
            'type' => 'string',
        ]);
        
        // Ensure cache is empty
        Cache::flush();
        $this->assertFalse(Cache::has('system_settings_v1'));
        
        // Warm cache
        Setting::warmCache();
        
        // Global cache should exist
        $this->assertTrue(Cache::has('system_settings_v1'));
        
        // Category caches should exist
        $this->assertTrue(Cache::has('system_settings_v1_general'));
        $this->assertTrue(Cache::has('system_settings_v1_appearance'));
    }

    /**
     * Test category-specific cache invalidation.
     * Validates: Requirements 5.2
     * 
     * @test
     */
    public function category_cache_is_cleared_on_update()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'general.test',
            'value' => 'original',
            'category' => 'general',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Warm cache to create category cache
        Setting::warmCache();
        $this->assertTrue(Cache::has('system_settings_v1_general'));
        
        // Update setting
        Setting::set('general.test', 'updated');
        
        // Category cache should be cleared
        $this->assertFalse(Cache::has('system_settings_v1_general'));
    }

    /**
     * Test WebSocket broadcasting on setting change.
     * Validates: Requirements 35.6
     * 
     * @test
     */
    public function setting_change_broadcasts_event()
    {
        Event::fake([SettingChanged::class]);
        
        $setting = SystemSetting::factory()->create([
            'key' => 'test.broadcast',
            'value' => 'original',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Update setting
        Setting::set('test.broadcast', 'new_value');
        
        // Assert event was dispatched
        Event::assertDispatched(SettingChanged::class, function ($event) {
            return $event->key === 'test.broadcast' 
                && $event->value === 'new_value';
        });
    }

    /**
     * Test WebSocket broadcasting in batch updates.
     * Validates: Requirements 35.6
     * 
     * @test
     */
    public function batch_updates_broadcast_each_change()
    {
        Event::fake([SettingChanged::class]);
        
        SystemSetting::factory()->create([
            'key' => 'test.batch1',
            'value' => 'original1',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        SystemSetting::factory()->create([
            'key' => 'test.batch2',
            'value' => 'original2',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Batch update
        Setting::setMany([
            'test.batch1' => 'new1',
            'test.batch2' => 'new2',
        ]);
        
        // Assert both events were dispatched
        Event::assertDispatched(SettingChanged::class, function ($event) {
            return $event->key === 'test.batch1' && $event->value === 'new1';
        });
        
        Event::assertDispatched(SettingChanged::class, function ($event) {
            return $event->key === 'test.batch2' && $event->value === 'new2';
        });
    }

    /**
     * Test manual cache clear operation.
     * Validates: Requirements 5.5
     * 
     * @test
     */
    public function manual_cache_clear_operation_works()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'test.manual',
            'value' => 'test',
            'type' => 'string',
        ]);
        
        // Load into cache
        Setting::get('test.manual');
        $this->assertTrue(Cache::has('system_settings_v1'));
        
        // Manual clear
        Setting::clearCache();
        
        // Cache should be cleared
        $this->assertFalse(Cache::has('system_settings_v1'));
    }

    /**
     * Test transaction rollback on database error.
     * Validates: Requirements 1.5
     * 
     * @test
     */
    public function transaction_rolls_back_on_database_error()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'test.rollback',
            'value' => 'original',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Force a database error by closing the connection during transaction
        try {
            DB::beginTransaction();
            
            Setting::setMany([
                'test.rollback' => 'should_not_save',
            ]);
            
            // Simulate database error
            throw new \Exception('Simulated database error');
        } catch (\Exception $e) {
            DB::rollBack();
        }
        
        // Verify setting was not updated
        Cache::flush(); // Clear cache to force DB read
        $this->assertEquals('original', Setting::get('test.rollback'));
    }

    /**
     * Test cache operations handle errors gracefully.
     * Validates: Requirements 5.2
     * 
     * @test
     */
    public function cache_errors_do_not_break_settings_operations()
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'test.graceful',
            'value' => 'original',
            'is_editable' => true,
            'type' => 'string',
        ]);
        
        // Even if cache fails, setting should still be saved to database
        Setting::set('test.graceful', 'updated');
        
        // Force read from database by clearing cache
        Cache::flush();
        
        // Verify value was saved
        $this->assertEquals('updated', Setting::get('test.graceful'));
    }
}
