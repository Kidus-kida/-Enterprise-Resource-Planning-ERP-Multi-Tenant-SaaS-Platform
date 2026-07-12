<?php

namespace Modules\Superadmin\Services;

use Illuminate\Support\Collection;
use Modules\Superadmin\Models\SettingsAuditLog;

/**
 * AuditService — centralized audit log management for system settings.
 *
 * Provides methods to:
 * - Log setting changes
 * - Retrieve audit logs with filters
 * - Export audit logs
 * - Mask sensitive values in audit logs
 */
class AuditService
{
    /**
     * Log a setting change.
     * 
     * @param string $key Setting key
     * @param mixed $oldValue Previous value
     * @param mixed $newValue New value
     * @param string $requestId Request identifier for grouping related changes
     * @return SettingsAuditLog
     */
    public function log(string $key, mixed $oldValue, mixed $newValue, string $requestId = null): SettingsAuditLog
    {
        return SettingsAuditLog::record(
            $key,
            $oldValue,
            $newValue,
            $requestId ?? \Illuminate\Support\Str::uuid()
        );
    }

    /**
     * Get audit logs for a specific setting key.
     * 
     * @param string $key Setting key
     * @param int $limit Maximum number of records to return
     * @return Collection
     */
    public function getForKey(string $key, int $limit = 50): Collection
    {
        return SettingsAuditLog::forKey($key)
            ->orderBy('changed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit logs.
     * 
     * @param int $days Number of days to look back
     * @return Collection
     */
    public function getRecent(int $days = 30): Collection
    {
        return SettingsAuditLog::recent($days)
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Search audit logs with multiple filters.
     * 
     * @param array $filters Associative array with keys: key, user_id, date_from, date_to, ip_address
     * @return Collection
     */
    public function search(array $filters): Collection
    {
        $query = SettingsAuditLog::query();

        // Filter by setting key
        if (!empty($filters['key'])) {
            $query->where('key', 'like', "%{$filters['key']}%");
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('changed_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('changed_at', '<=', $filters['date_to']);
        }

        // Filter by IP address
        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        return $query->orderBy('changed_at', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get();
    }

    /**
     * Get audit logs for a specific user.
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of records to return
     * @return Collection
     */
    public function getByUser(int $userId, int $limit = 50): Collection
    {
        return SettingsAuditLog::byUser($userId)
            ->orderBy('changed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Export audit logs to CSV format.
     * 
     * @param array $filters Same filters as search() method
     * @return string CSV string
     */
    public function exportCsv(array $filters = []): string
    {
        $logs = $this->search($filters);
        
        $csv = "Setting Key,Old Value,New Value,User,IP Address,Browser,Device,Changed At\n";
        
        foreach ($logs as $log) {
            $userName = $log->user ? $log->user->name : 'System';
            
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $this->escapeCsv($log->key),
                $this->escapeCsv($this->maskSensitiveValue($log->key, $log->old_value)),
                $this->escapeCsv($this->maskSensitiveValue($log->key, $log->new_value)),
                $this->escapeCsv($userName),
                $this->escapeCsv($log->ip_address),
                $this->escapeCsv($log->browser),
                $this->escapeCsv($log->device),
                $log->changed_at->toDateTimeString()
            );
        }
        
        return $csv;
    }

    /**
     * Mask sensitive values in audit logs.
     * 
     * @param string $key Setting key
     * @param string $value Setting value
     * @return string Masked value if sensitive, original value otherwise
     */
    private function maskSensitiveValue(string $key, ?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // Check if the setting is sensitive by looking it up
        $setting = \Modules\Superadmin\Models\SystemSetting::where('key', $key)->first();
        
        if ($setting && $setting->is_sensitive) {
            return '••••••••';
        }

        return $value;
    }

    /**
     * Escape CSV value.
     * 
     * @param string|null $value Value to escape
     * @return string Escaped value
     */
    private function escapeCsv(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return str_replace('"', '""', $value);
    }

    /**
     * Get audit log statistics for a date range.
     * 
     * @param string|null $dateFrom Start date
     * @param string|null $dateTo End date
     * @return array Statistics array
     */
    public function getStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = SettingsAuditLog::query();

        if ($dateFrom) {
            $query->where('changed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('changed_at', '<=', $dateTo);
        }

        $total = $query->count();
        $uniqueUsers = $query->distinct('user_id')->count('user_id');
        $uniqueKeys = $query->distinct('key')->count('key');

        return [
            'total_changes' => $total,
            'unique_users' => $uniqueUsers,
            'unique_settings' => $uniqueKeys,
        ];
    }

    /**
     * Delete old audit logs beyond retention period.
     * 
     * @param int $retentionDays Number of days to retain
     * @return int Number of records deleted
     */
    public function pruneOldLogs(int $retentionDays = 365): int
    {
        $cutoffDate = now()->subDays($retentionDays);
        
        return SettingsAuditLog::where('changed_at', '<', $cutoffDate)->delete();
    }
}
