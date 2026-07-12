<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

/**
 * SystemSetting Model — represents one row in the key-value settings engine.
 *
 * @property int    $id
 * @property string $category
 * @property string $section
 * @property string $key
 * @property string $value
 * @property string $type
 * @property string $input_type
 * @property string $label
 * @property string $description
 * @property array  $options
 * @property string $validation_rules
 * @property string $default_value
 * @property string $depends_on
 * @property bool   $is_public
 * @property bool   $is_editable
 * @property bool   $is_sensitive
 * @property bool   $is_system
 * @property int    $sort_order
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'category',
        'section',
        'key',
        'value',
        'type',
        'input_type',
        'label',
        'description',
        'options',
        'validation_rules',
        'default_value',
        'depends_on',
        'is_public',
        'is_editable',
        'is_sensitive',
        'is_system',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'options'     => 'array',
        'is_public'   => 'boolean',
        'is_editable' => 'boolean',
        'is_sensitive'=> 'boolean',
        'is_system'   => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get the typed value (decrypted if sensitive, cast to PHP type).
     */
    public function getTypedValueAttribute(): mixed
    {
        $raw = $this->value;

        // Decrypt sensitive values
        if ($this->is_sensitive && !empty($raw)) {
            try {
                $raw = Crypt::decryptString($raw);
            } catch (\Exception) {
                // Return as-is if decryption fails (e.g. value was set before encryption)
            }
        }

        return match ($this->type) {
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $raw,
            'float'   => (float) $raw,
            'json'    => json_decode($raw, true) ?? [],
            'image'   => ($raw ? Storage::url($raw) : null),
            default   => $raw,
        };
    }

    /**
     * Get display value — masked if sensitive.
     */
    public function getDisplayValueAttribute(): string
    {
        if ($this->is_sensitive && !empty($this->value)) {
            return '••••••••';
        }
        return (string) ($this->type === 'image' ? $this->typed_value : $this->value);
    }

    /**
     * Get dependency metadata as array.
     * Returns null if no dependency, otherwise ['key' => 'setting.key', 'value' => 'expected_value']
     */
    public function getDependencyMetAttribute(): ?array
    {
        if (!$this->depends_on || !str_contains($this->depends_on, ':')) {
            return null;
        }
        
        [$key, $expectedValue] = explode(':', $this->depends_on, 2);
        return ['key' => $key, 'value' => $expectedValue];
    }

    /**
     * Check if dependency condition is satisfied.
     * Returns true if no dependency or if dependency is met.
     */
    public function isDependencySatisfied(): bool
    {
        $dep = $this->dependency_met;
        
        if (!$dep) {
            return true;
        }
        
        // Get actual value of the dependent setting
        $actualValue = app(\Modules\Superadmin\Services\SettingsService::class)
            ->get($dep['key']);
        
        return (string) $actualValue === (string) $dep['value'];
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
