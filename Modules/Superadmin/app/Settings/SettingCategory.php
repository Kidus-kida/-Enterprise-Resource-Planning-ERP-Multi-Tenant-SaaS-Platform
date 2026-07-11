<?php

namespace Modules\Superadmin\Settings;

/**
 * SettingCategory — constants for all setting categories.
 * Use these everywhere instead of raw strings.
 */
final class SettingCategory
{
    const GENERAL        = 'general';
    const COMPANY        = 'company';
    const APPEARANCE     = 'appearance';
    const AUTHENTICATION = 'authentication';
    const LOCALIZATION   = 'localization';
    const EMAIL          = 'email';
    const NOTIFICATION   = 'notification';
    const STORAGE        = 'storage';
    const BACKUP         = 'backup';
    const SECURITY       = 'security';
    const MODULES        = 'modules';
    const INTEGRATION    = 'integration';
    const MAINTENANCE    = 'maintenance';
    const LOGS           = 'logs';
    const LICENSE        = 'license';
    const ADVANCED       = 'advanced';
    const WHITELABEL     = 'whitelabel';
    const DASHBOARD      = 'dashboard';
    const MENU           = 'menu';

    /**
     * All categories in sidebar display order.
     */
    const ALL = [
        self::GENERAL,
        self::COMPANY,
        self::APPEARANCE,
        self::AUTHENTICATION,
        self::LOCALIZATION,
        self::EMAIL,
        self::NOTIFICATION,
        self::STORAGE,
        self::BACKUP,
        self::SECURITY,
        self::MODULES,
        self::INTEGRATION,
        self::MAINTENANCE,
        self::LOGS,
        self::LICENSE,
        self::ADVANCED,
        self::WHITELABEL,
        self::DASHBOARD,
        self::MENU,
    ];

    /** Human-readable labels for each category */
    const LABELS = [
        self::GENERAL        => 'General Settings',
        self::COMPANY        => 'Company Settings',
        self::APPEARANCE     => 'Appearance',
        self::AUTHENTICATION => 'Authentication',
        self::LOCALIZATION   => 'Localization',
        self::EMAIL          => 'Email Settings',
        self::NOTIFICATION   => 'Notifications',
        self::STORAGE        => 'File Storage',
        self::BACKUP         => 'Backup & Restore',
        self::SECURITY       => 'Security',
        self::MODULES        => 'Modules',
        self::INTEGRATION    => 'Integrations',
        self::MAINTENANCE    => 'Maintenance',
        self::LOGS           => 'Logs',
        self::LICENSE        => 'License',
        self::ADVANCED       => 'Advanced Settings',
        self::WHITELABEL     => 'White-Label Branding',
        self::DASHBOARD      => 'Dashboard Builder',
        self::MENU           => 'Menu Builder',
    ];

    /** Icons (Font Awesome classes) for each category */
    const ICONS = [
        self::GENERAL        => 'fa-solid fa-gear',
        self::COMPANY        => 'fa-solid fa-building',
        self::APPEARANCE     => 'fa-solid fa-palette',
        self::AUTHENTICATION => 'fa-solid fa-shield-halved',
        self::LOCALIZATION   => 'fa-solid fa-globe',
        self::EMAIL          => 'fa-solid fa-envelope',
        self::NOTIFICATION   => 'fa-solid fa-bell',
        self::STORAGE        => 'fa-solid fa-hard-drive',
        self::BACKUP         => 'fa-solid fa-database',
        self::SECURITY       => 'fa-solid fa-lock',
        self::MODULES        => 'fa-solid fa-cubes',
        self::INTEGRATION    => 'fa-solid fa-plug',
        self::MAINTENANCE    => 'fa-solid fa-wrench',
        self::LOGS           => 'fa-solid fa-file-lines',
        self::LICENSE        => 'fa-solid fa-key',
        self::ADVANCED       => 'fa-solid fa-terminal',
        self::WHITELABEL     => 'fa-solid fa-tag',
        self::DASHBOARD      => 'fa-solid fa-table-columns',
        self::MENU           => 'fa-solid fa-bars',
    ];

    private function __construct() {}
}
