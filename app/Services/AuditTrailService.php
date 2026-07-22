<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AuditTrailService
{
    /**
     * Check if audit trail is enabled
     */
    private static function isEnabled(): bool
    {
        return Config::get('audit.enabled', true);
    }

    /**
     * Check if specific activity type should be logged
     */
    private static function shouldLogActivity(string $activityType): bool
    {
        return Config::get("audit.activity_logging.{$activityType}", true);
    }

    /**
     * Log user authentication activity
     */
    public static function logAuth(string $action, ?int $userId = null, ?int $cooperationId = null): void
    {
        if (!self::isEnabled() || !self::shouldLogActivity('authentication')) {
            return;
        }

        $cooperationId = $cooperationId ?? (Auth::user()?->cooperation_id);
        
        AuthLog::create([
            'cooperation_id' => $cooperationId,
            'user_id' => $userId ?? Auth::id(),
            'ip_address' => self::getClientIpAddress(),
            'user_agent' => Request::userAgent(),
            'action' => $action,
        ]);
    }

    /**
     * Log general user activity
     */
    public static function logActivity(
        string $action, 
        string $module, 
        string $description, 
        ?int $userId = null, 
        ?int $cooperationId = null
    ): void {
        if (!self::isEnabled()) {
            return;
        }

        $cooperationId = $cooperationId ?? (Auth::user()?->cooperation_id);
        
        ActivityLog::create([
            'cooperation_id' => $cooperationId,
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => self::getClientIpAddress(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log data changes (create, update, delete)
     */
    public static function logDataChange(
        Model $model, 
        string $action, 
        ?array $oldValues = null, 
        ?array $newValues = null,
        ?int $userId = null,
        ?int $cooperationId = null
    ): void {
        if (!self::isEnabled() || !self::shouldLogActivity('data_changes')) {
            return;
        }

        // Check if model is excluded
        $excludedModels = Config::get('audit.excluded_models', []);
        if (in_array(get_class($model), $excludedModels)) {
            return;
        }

        $cooperationId = $cooperationId ?? (Auth::user()?->cooperation_id);
        
        // Get the table name from the model
        $tableName = $model->getTable();
        
        // Prepare values based on action
        switch ($action) {
            case 'create':
                $newValues = $newValues ?? $model->getAttributes();
                $oldValues = null;
                break;
            case 'update':
                $oldValues = $oldValues ?? $model->getOriginal();
                $newValues = $newValues ?? $model->getDirty();
                break;
            case 'delete':
                $oldValues = $oldValues ?? $model->getOriginal();
                $newValues = null;
                break;
        }

        // Remove sensitive fields
        $sensitiveFields = Config::get('audit.sensitive_fields', []);
        foreach ($sensitiveFields as $field) {
            unset($oldValues[$field], $newValues[$field]);
        }

        DataChangeLog::create([
            'cooperation_id' => $cooperationId,
            'user_id' => $userId ?? Auth::id(),
            'table_name' => $tableName,
            'record_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Log file upload/download activity
     */
    public static function logFileActivity(string $action, string $fileName, string $filePath): void
    {
        if (!self::shouldLogActivity('file_operations')) {
            return;
        }

        self::logActivity(
            $action,
            'file_management',
            "File {$action}: {$fileName} at {$filePath}"
        );
    }

    /**
     * Log report generation
     */
    public static function logReportGeneration(string $reportType, array $parameters = []): void
    {
        if (!self::shouldLogActivity('report_generation')) {
            return;
        }

        $description = "Generated {$reportType} report";
        if (!empty($parameters)) {
            $description .= " with parameters: " . json_encode($parameters);
        }

        self::logActivity(
            'generate_report',
            'reports',
            $description
        );
    }

    /**
     * Log bulk operations
     */
    public static function logBulkOperation(string $operation, string $module, int $affectedCount, array $details = []): void
    {
        $description = "Bulk {$operation} in {$module}: {$affectedCount} records affected";
        if (!empty($details)) {
            $description .= " - Details: " . json_encode($details);
        }

        self::logActivity(
            "bulk_{$operation}",
            $module,
            $description
        );
    }

    /**
     * Log access to sensitive data
     */
    public static function logSensitiveDataAccess(string $dataType, int $recordId, string $action = 'view'): void
    {
        self::logActivity(
            $action,
            'sensitive_data',
            "Accessed {$dataType} data (ID: {$recordId})"
        );
    }

    /**
     * Log system configuration changes
     */
    public static function logConfigChange(string $configKey, $oldValue, $newValue): void
    {
        self::logActivity(
            'config_change',
            'system',
            "Configuration '{$configKey}' changed from '{$oldValue}' to '{$newValue}'"
        );
    }

    /**
     * Log permission changes
     */
    public static function logPermissionChange(int $targetUserId, string $permission, string $action): void
    {
        self::logActivity(
            'permission_change',
            'user_management',
            "Permission '{$permission}' {$action} for user ID: {$targetUserId}"
        );
    }

    /**
     * Get client IP address considering proxies
     */
    private static function getClientIpAddress(): ?string
    {
        if (Config::get('audit.ip_address.anonymize', false)) {
            return self::anonymizeIpAddress(Request::ip());
        }

        return Request::ip();
    }

    /**
     * Anonymize IP address for privacy
     */
    private static function anonymizeIpAddress(?string $ipAddress): ?string
    {
        if (!$ipAddress) {
            return null;
        }

        // For IPv4, mask the last octet
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\d+$/', 'XXX', $ipAddress);
        }

        // For IPv6, mask the last 4 groups
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ipAddress);
            $parts[count($parts) - 1] = 'XXXX';
            $parts[count($parts) - 2] = 'XXXX';
            return implode(':', $parts);
        }

        return $ipAddress;
    }

    /**
     * Check if user agent should be excluded
     */
    public static function shouldExcludeUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        $excludedUserAgents = Config::get('audit.excluded_user_agents', []);
        
        foreach ($excludedUserAgents as $excluded) {
            if (stripos($userAgent, $excluded) !== false) {
                return true;
            }
        }

        return false;
    }
}
