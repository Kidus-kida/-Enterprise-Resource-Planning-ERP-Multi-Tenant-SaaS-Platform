<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * SettingsAuditLog — tracks every setting change.
 */
class SettingsAuditLog extends Model
{
    protected $table = 'settings_audit_logs';

    protected $fillable = [
        'key',
        'old_value',
        'new_value',
        'user_id',
        'ip_address',
        'user_agent',
        'browser',
        'device',
        'request_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Create an audit record from the current request context.
     * Automatically masks sensitive setting values.
     */
    public static function record(
        string $key,
        mixed  $oldValue,
        mixed  $newValue,
        string $requestId
    ): self {
        $userAgent = request()->userAgent() ?? '';
        
        // Check if this setting is sensitive
        $setting = SystemSetting::where('key', $key)->first();
        $isSensitive = $setting && $setting->is_sensitive;
        
        // Mask sensitive values
        $maskedOldValue = $isSensitive && !empty($oldValue) ? '••••••••' : $oldValue;
        $maskedNewValue = $isSensitive && !empty($newValue) ? '••••••••' : $newValue;

        return static::create([
            'key'        => $key,
            'old_value'  => is_array($maskedOldValue) ? json_encode($maskedOldValue) : (string) $maskedOldValue,
            'new_value'  => is_array($maskedNewValue) ? json_encode($maskedNewValue) : (string) $maskedNewValue,
            'user_id'    => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => $userAgent,
            'browser'    => static::parseBrowser($userAgent),
            'device'     => static::parseDevice($userAgent),
            'request_id' => $requestId,
            'changed_at' => now(),
        ]);
    }

    private static function parseBrowser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg')     => 'Edge',
            str_contains($userAgent, 'OPR')     => 'Opera',
            str_contains($userAgent, 'Chrome')  => 'Chrome',
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Safari')  => 'Safari',
            str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident') => 'IE',
            default => 'Unknown',
        };
    }

    private static function parseDevice(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobi') || str_contains($userAgent, 'Android')) {
            return 'mobile';
        }
        if (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            return 'tablet';
        }
        return 'desktop';
    }
}
