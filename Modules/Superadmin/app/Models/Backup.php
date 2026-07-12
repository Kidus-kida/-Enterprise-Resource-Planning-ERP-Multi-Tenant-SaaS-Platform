<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * Backup Model — represents a system backup record.
 *
 * @property int    $id
 * @property string $filename
 * @property string $path
 * @property int    $size
 * @property string $type
 * @property bool   $includes_database
 * @property bool   $includes_files
 * @property array  $metadata
 * @property int    $created_by
 * @property \Carbon\Carbon $created_at
 */
class Backup extends Model
{
    protected $table = 'backups';

    /**
     * Indicates if the model should use timestamps.
     * Only created_at is used; updated_at is not needed for backups.
     */
    public $timestamps = false;

    protected $fillable = [
        'filename',
        'path',
        'size',
        'type',
        'includes_database',
        'includes_files',
        'metadata',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'includes_database' => 'boolean',
        'includes_files' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        return $this->formatBytes($this->size);
    }

    /**
     * Get human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'manual' => 'Manual Backup',
            'scheduled' => 'Scheduled Backup',
            'pre-restore' => 'Pre-Restore Backup',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get contents description.
     */
    public function getContentsDescriptionAttribute(): string
    {
        $parts = [];
        
        if ($this->includes_database) {
            $parts[] = 'Database';
        }
        
        if ($this->includes_files) {
            $parts[] = 'Files';
        }
        
        return empty($parts) ? 'Empty' : implode(' + ', $parts);
    }

    /**
     * Check if the backup file exists on disk.
     */
    public function exists(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('backups')->exists($this->path);
    }

    /**
     * Get the full path to the backup file.
     */
    public function getFullPathAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('backups')->path($this->path);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }

    public function scopeScheduled($query)
    {
        return $query->where('type', 'scheduled');
    }

    public function scopePreRestore($query)
    {
        return $query->where('type', 'pre-restore');
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Format bytes to human-readable size.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get age in human-readable format.
     */
    public function getAgeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if backup is old (older than specified days).
     */
    public function isOlderThan(int $days): bool
    {
        return $this->created_at->lt(now()->subDays($days));
    }

    /**
     * Get metadata value by key.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set metadata value by key.
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }
}
