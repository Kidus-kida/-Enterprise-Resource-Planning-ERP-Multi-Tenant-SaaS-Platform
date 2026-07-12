<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Superadmin\Models\SystemSetting;
use Modules\Superadmin\Settings\SettingCategory;

/**
 * SystemSettingsSeeder — seeds all default ERP settings.
 *
 * Idempotent: uses updateOrCreate so safe to run multiple times.
 * Add new settings here instead of modifying the DB schema.
 */
class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = $this->getSettings();
        $order    = 0;

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'sort_order' => $order++,
                    'is_editable' => $setting['is_editable'] ?? true,
                    'is_public'   => $setting['is_public']   ?? false,
                    'is_sensitive'=> $setting['is_sensitive'] ?? false,
                    'is_system'   => $setting['is_system']   ?? false,
                    'default_value' => $setting['value'] ?? null,
                ])
            );
        }

        $this->command->info('System settings seeded: ' . count($settings) . ' settings.');
    }

    // =========================================================================
    // Settings Definitions
    // =========================================================================

    private function getSettings(): array
    {
        return array_merge(
            $this->generalSettings(),
            $this->companySettings(),
            $this->appearanceSettings(),
            $this->authenticationSettings(),
            $this->localizationSettings(),
            $this->emailSettings(),
            $this->notificationSettings(),
            $this->storageSettings(),
            $this->backupSettings(),
            $this->securitySettings(),
            $this->modulesSettings(),
            $this->integrationSettings(),
            $this->maintenanceSettings(),
            $this->logsSettings(),
            $this->licenseSettings(),
            $this->advancedSettings(),
            $this->whitelabelSettings(),
            $this->dashboardSettings(),
            $this->menuSettings(),
        );
    }

    // =========================================================================
    // 1. GENERAL
    // =========================================================================
    private function generalSettings(): array
    {
        $c = SettingCategory::GENERAL;
        return [
            ['category'=>$c,'section'=>'system','key'=>'general.system_name',         'label'=>'System Name',            'value'=>'ERP System',        'type'=>'string','input_type'=>'text',   'validation_rules'=>'required|string|max:100','is_public'=>true],
            ['category'=>$c,'section'=>'system','key'=>'general.short_name',           'label'=>'Short Name',             'value'=>'ERP',               'type'=>'string','input_type'=>'text',   'validation_rules'=>'nullable|string|max:20', 'is_public'=>true],
            ['category'=>$c,'section'=>'system','key'=>'general.system_version',       'label'=>'System Version',         'value'=>'1.0.0',             'type'=>'string','input_type'=>'text',   'validation_rules'=>'nullable|string|max:20'],
            ['category'=>$c,'section'=>'system','key'=>'general.footer_text',          'label'=>'Footer Text',            'value'=>'© 2026 ERP System. All rights reserved.','type'=>'string','input_type'=>'text','validation_rules'=>'nullable|string|max:255'],
            ['category'=>$c,'section'=>'system','key'=>'general.copyright',            'label'=>'Copyright',              'value'=>'© 2026 ERP System', 'type'=>'string','input_type'=>'text',   'validation_rules'=>'nullable|string|max:100'],
            ['category'=>$c,'section'=>'system','key'=>'general.system_email',         'label'=>'System Email',           'value'=>'system@example.com','type'=>'string','input_type'=>'email',  'validation_rules'=>'nullable|email|max:150', 'is_public'=>true],
            ['category'=>$c,'section'=>'support','key'=>'general.support_email',       'label'=>'Support Email',          'value'=>'support@example.com','type'=>'string','input_type'=>'email', 'validation_rules'=>'nullable|email|max:150', 'is_public'=>true],
            ['category'=>$c,'section'=>'support','key'=>'general.support_phone',       'label'=>'Support Phone',          'value'=>'',                  'type'=>'string','input_type'=>'text',   'validation_rules'=>'nullable|string|max:30',  'is_public'=>true],
            ['category'=>$c,'section'=>'defaults','key'=>'general.timezone',           'label'=>'Timezone',               'value'=>'UTC',               'type'=>'string','input_type'=>'select', 'validation_rules'=>'required|timezone',       'is_public'=>true, 'options'=>$this->timezoneOptions()],
            ['category'=>$c,'section'=>'defaults','key'=>'general.date_format',        'label'=>'Date Format',            'value'=>'d/m/Y',             'type'=>'string','input_type'=>'select', 'validation_rules'=>'required|string',          'is_public'=>true, 'options'=>[['label'=>'DD/MM/YYYY','value'=>'d/m/Y'],['label'=>'MM/DD/YYYY','value'=>'m/d/Y'],['label'=>'YYYY-MM-DD','value'=>'Y-m-d'],['label'=>'DD-MM-YYYY','value'=>'d-m-Y'],['label'=>'D M, Y','value'=>'j F, Y']]],
            ['category'=>$c,'section'=>'defaults','key'=>'general.time_format',        'label'=>'Time Format',            'value'=>'H:i',               'type'=>'string','input_type'=>'select', 'validation_rules'=>'required|string',          'is_public'=>true, 'options'=>[['label'=>'24-Hour (HH:MM)','value'=>'H:i'],['label'=>'12-Hour (hh:MM AM/PM)','value'=>'h:i A']]],
            ['category'=>$c,'section'=>'defaults','key'=>'general.currency',           'label'=>'Default Currency',       'value'=>'ETB',               'type'=>'string','input_type'=>'select', 'validation_rules'=>'required|string|max:10',   'is_public'=>true, 'options'=>$this->currencyOptions()],
            ['category'=>$c,'section'=>'defaults','key'=>'general.language',           'label'=>'Default Language',       'value'=>'en',                'type'=>'string','input_type'=>'select', 'validation_rules'=>'required|string|max:10',   'is_public'=>true, 'options'=>[['label'=>'English','value'=>'en'],['label'=>'Amharic','value'=>'am'],['label'=>'Arabic','value'=>'ar'],['label'=>'French','value'=>'fr']]],
            ['category'=>$c,'section'=>'defaults','key'=>'general.week_start',         'label'=>'Week Start Day',         'value'=>'1',                 'type'=>'string','input_type'=>'select', 'validation_rules'=>'required|in:0,1,6',         'is_public'=>true, 'options'=>[['label'=>'Sunday','value'=>'0'],['label'=>'Monday','value'=>'1'],['label'=>'Saturday','value'=>'6']]],
            ['category'=>$c,'section'=>'defaults','key'=>'general.default_country',    'label'=>'Default Country',        'value'=>'ET',                'type'=>'string','input_type'=>'text',   'validation_rules'=>'nullable|string|max:3',    'is_public'=>true],
            ['category'=>$c,'section'=>'defaults','key'=>'general.records_per_page',   'label'=>'Records Per Page',       'value'=>'25',                'type'=>'integer','input_type'=>'select','validation_rules'=>'required|integer|in:10,25,50,100','is_public'=>true,'options'=>[['label'=>'10','value'=>'10'],['label'=>'25','value'=>'25'],['label'=>'50','value'=>'50'],['label'=>'100','value'=>'100']]],
            ['category'=>$c,'section'=>'defaults','key'=>'general.default_landing_page','label'=>'Default Landing Page', 'value'=>'/dashboard',         'type'=>'string','input_type'=>'text',   'validation_rules'=>'nullable|string|max:100'],
        ];
    }

    // =========================================================================
    // 2. COMPANY
    // =========================================================================
    private function companySettings(): array
    {
        $c = SettingCategory::COMPANY;
        return [
            ['category'=>$c,'section'=>'identity','key'=>'company.name',            'label'=>'Company Name',         'value'=>'My Company Ltd',    'type'=>'string','input_type'=>'text',  'validation_rules'=>'required|string|max:150','is_public'=>true],
            ['category'=>$c,'section'=>'identity','key'=>'company.logo',            'label'=>'Company Logo',         'value'=>null,                'type'=>'image', 'input_type'=>'image', 'validation_rules'=>'nullable|image|max:2048',  'is_public'=>true],
            ['category'=>$c,'section'=>'identity','key'=>'company.dark_logo',       'label'=>'Dark Mode Logo',       'value'=>null,                'type'=>'image', 'input_type'=>'image', 'validation_rules'=>'nullable|image|max:2048',  'is_public'=>true],
            ['category'=>$c,'section'=>'identity','key'=>'company.favicon',         'label'=>'Favicon',              'value'=>null,                'type'=>'image', 'input_type'=>'image', 'validation_rules'=>'nullable|mimes:ico,png|max:512','is_public'=>true],
            ['category'=>$c,'section'=>'identity','key'=>'company.description',     'label'=>'Company Description',  'value'=>'',                  'type'=>'string','input_type'=>'textarea','validation_rules'=>'nullable|string|max:500', 'is_public'=>true],
            ['category'=>$c,'section'=>'contact', 'key'=>'company.email',           'label'=>'Company Email',        'value'=>'info@company.com',  'type'=>'string','input_type'=>'email', 'validation_rules'=>'nullable|email|max:150',  'is_public'=>true],
            ['category'=>$c,'section'=>'contact', 'key'=>'company.phone',           'label'=>'Phone',                'value'=>'',                  'type'=>'string','input_type'=>'text',  'validation_rules'=>'nullable|string|max:30',  'is_public'=>true],
            ['category'=>$c,'section'=>'contact', 'key'=>'company.website',         'label'=>'Website',              'value'=>'',                  'type'=>'string','input_type'=>'url',   'validation_rules'=>'nullable|url|max:200',    'is_public'=>true],
            ['category'=>$c,'section'=>'address', 'key'=>'company.address',         'label'=>'Address',              'value'=>'',                  'type'=>'string','input_type'=>'textarea','validation_rules'=>'nullable|string|max:300','is_public'=>true],
            ['category'=>$c,'section'=>'address', 'key'=>'company.city',            'label'=>'City',                 'value'=>'',                  'type'=>'string','input_type'=>'text',  'validation_rules'=>'nullable|string|max:100', 'is_public'=>true],
            ['category'=>$c,'section'=>'address', 'key'=>'company.country',         'label'=>'Country',              'value'=>'Ethiopia',           'type'=>'string','input_type'=>'text',  'validation_rules'=>'nullable|string|max:100', 'is_public'=>true],
            ['category'=>$c,'section'=>'address', 'key'=>'company.postal_code',     'label'=>'Postal Code',          'value'=>'',                  'type'=>'string','input_type'=>'text',  'validation_rules'=>'nullable|string|max:20'],
            ['category'=>$c,'section'=>'legal',   'key'=>'company.tax_number',      'label'=>'Tax Number',           'value'=>'',                  'type'=>'string','input_type'=>'text',  'validation_rules'=>'nullable|string|max:50'],
            ['category'=>$c,'section'=>'legal',   'key'=>'company.registration_number','label'=>'Registration Number','value'=>'',                 'type'=>'string','input_type'=>'text',  'validation_rules'=>'nullable|string|max:50'],
            ['category'=>$c,'section'=>'branding','key'=>'company.banner',          'label'=>'Company Banner',       'value'=>null,                'type'=>'image', 'input_type'=>'image', 'validation_rules'=>'nullable|image|max:4096'],
        ];
    }

    // =========================================================================
    // 3. APPEARANCE
    // =========================================================================
    private function appearanceSettings(): array
    {
        $c = SettingCategory::APPEARANCE;
        return [
            // Theme
            ['category'=>$c,'section'=>'theme',  'key'=>'appearance.theme',              'label'=>'Active Theme',         'value'=>'default',     'type'=>'string','input_type'=>'select', 'is_public'=>true,'options'=>[['label'=>'Default','value'=>'default'],['label'=>'Blue','value'=>'blue'],['label'=>'Dark','value'=>'dark'],['label'=>'Corporate','value'=>'corporate'],['label'=>'Green','value'=>'green'],['label'=>'Custom','value'=>'custom']]],
            ['category'=>$c,'section'=>'theme',  'key'=>'appearance.dark_mode',          'label'=>'Dark Mode',            'value'=>'0',           'type'=>'boolean','input_type'=>'switch', 'is_public'=>true],
            ['category'=>$c,'section'=>'theme',  'key'=>'appearance.rtl',                'label'=>'RTL Layout',           'value'=>'0',           'type'=>'boolean','input_type'=>'switch', 'is_public'=>true],
            // Colors (shown only when theme = custom)
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.primary_color',      'label'=>'Primary Color',        'value'=>'#4e73df',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.secondary_color',    'label'=>'Secondary Color',      'value'=>'#858796',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.accent_color',       'label'=>'Accent Color',         'value'=>'#f6c23e',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.sidebar_color',      'label'=>'Sidebar Color',        'value'=>'#2c3e50',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.sidebar_text_color', 'label'=>'Sidebar Text Color',   'value'=>'#ffffff',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.navbar_color',       'label'=>'Navbar Color',         'value'=>'#ffffff',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.header_color',       'label'=>'Header Color',         'value'=>'#f8f9fa',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.danger_color',       'label'=>'Danger Color',         'value'=>'#e74a3b',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.warning_color',      'label'=>'Warning Color',        'value'=>'#f6c23e',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.success_color',      'label'=>'Success Color',        'value'=>'#1cc88a',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            ['category'=>$c,'section'=>'colors', 'key'=>'appearance.info_color',         'label'=>'Info Color',           'value'=>'#36b9cc',     'type'=>'string','input_type'=>'color',  'is_public'=>true,'depends_on'=>'appearance.theme:custom'],
            // Layout
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.sidebar_width',      'label'=>'Sidebar Width (px)',   'value'=>'250',         'type'=>'integer','input_type'=>'number', 'validation_rules'=>'nullable|integer|min:160|max:400'],
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.border_radius',      'label'=>'Border Radius (px)',   'value'=>'4',           'type'=>'integer','input_type'=>'number', 'validation_rules'=>'nullable|integer|min:0|max:24'],
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.container_width',    'label'=>'Container Width',      'value'=>'fluid',       'type'=>'string','input_type'=>'select', 'options'=>[['label'=>'Fluid (Full Width)','value'=>'fluid'],['label'=>'Fixed (1200px)','value'=>'fixed'],['label'=>'Compact (960px)','value'=>'compact']]],
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.sidebar_collapse',   'label'=>'Collapsible Sidebar',  'value'=>'1',           'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.header_fixed',       'label'=>'Fixed Header',         'value'=>'1',           'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.sidebar_fixed',      'label'=>'Fixed Sidebar',        'value'=>'1',           'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'layout', 'key'=>'appearance.compact_mode',       'label'=>'Compact Mode',         'value'=>'0',           'type'=>'boolean','input_type'=>'switch'],
            // Typography
            ['category'=>$c,'section'=>'typography','key'=>'appearance.font_family',     'label'=>'Font Family',          'value'=>'Inter, sans-serif','type'=>'string','input_type'=>'select','options'=>[['label'=>'Inter','value'=>'Inter, sans-serif'],['label'=>'Roboto','value'=>'Roboto, sans-serif'],['label'=>'Poppins','value'=>'Poppins, sans-serif'],['label'=>'Open Sans','value'=>'Open Sans, sans-serif'],['label'=>'System Default','value'=>'system-ui, sans-serif']],'is_public'=>true],
            ['category'=>$c,'section'=>'typography','key'=>'appearance.font_size',       'label'=>'Base Font Size',       'value'=>'14px',        'type'=>'string','input_type'=>'select','options'=>[['label'=>'12px','value'=>'12px'],['label'=>'13px','value'=>'13px'],['label'=>'14px','value'=>'14px'],['label'=>'15px','value'=>'15px'],['label'=>'16px','value'=>'16px']],'is_public'=>true],
            // Effects
            ['category'=>$c,'section'=>'effects', 'key'=>'appearance.animations',        'label'=>'Animations',           'value'=>'1',           'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'effects', 'key'=>'appearance.shadows',           'label'=>'Card Shadows',         'value'=>'1',           'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'effects', 'key'=>'appearance.glass_effect',      'label'=>'Glass Effect',         'value'=>'0',           'type'=>'boolean','input_type'=>'switch'],
            // Backgrounds
            ['category'=>$c,'section'=>'backgrounds','key'=>'appearance.app_background', 'label'=>'App Background Image', 'value'=>null,          'type'=>'image', 'input_type'=>'image'],
            ['category'=>$c,'section'=>'backgrounds','key'=>'appearance.login_background','label'=>'Login Background',    'value'=>null,          'type'=>'image', 'input_type'=>'image'],
            // Login
            ['category'=>$c,'section'=>'login',   'key'=>'appearance.login_logo',        'label'=>'Login Logo',           'value'=>null,          'type'=>'image', 'input_type'=>'image'],
            ['category'=>$c,'section'=>'login',   'key'=>'appearance.login_title',       'label'=>'Login Title',          'value'=>'Welcome Back!','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'login',   'key'=>'appearance.login_subtitle',    'label'=>'Login Subtitle',       'value'=>'Sign in to your account','type'=>'string','input_type'=>'text'],
            // Loader
            ['category'=>$c,'section'=>'loader',  'key'=>'appearance.loader_enabled',    'label'=>'Page Loader',          'value'=>'1',           'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'loader',  'key'=>'appearance.loader_color',      'label'=>'Loader Color',         'value'=>'#4e73df',     'type'=>'string','input_type'=>'color'],
            ['category'=>$c,'section'=>'loader',  'key'=>'appearance.loader_text',       'label'=>'Loader Text',          'value'=>'Loading...',  'type'=>'string','input_type'=>'text'],
            // Custom code
            ['category'=>$c,'section'=>'custom',  'key'=>'appearance.custom_css',        'label'=>'Custom CSS',           'value'=>'',            'type'=>'string','input_type'=>'code'],
            ['category'=>$c,'section'=>'custom',  'key'=>'appearance.custom_js',         'label'=>'Custom JavaScript',    'value'=>'',            'type'=>'string','input_type'=>'code'],
        ];
    }

    // =========================================================================
    // 4. AUTHENTICATION
    // =========================================================================
    private function authenticationSettings(): array
    {
        $c = SettingCategory::AUTHENTICATION;
        return [
            ['category'=>$c,'section'=>'login',    'key'=>'authentication.remember_me',       'label'=>'Remember Me',           'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'login',    'key'=>'authentication.forgot_password',   'label'=>'Forgot Password Link',  'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'login',    'key'=>'authentication.registration',      'label'=>'Allow Registration',    'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'login',    'key'=>'authentication.email_verification','label'=>'Email Verification',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'login',    'key'=>'authentication.captcha',           'label'=>'CAPTCHA',               'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'session',  'key'=>'authentication.session_timeout',   'label'=>'Session Timeout (min)', 'value'=>'60','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:1440'],
            ['category'=>$c,'section'=>'session',  'key'=>'authentication.auto_logout',       'label'=>'Auto Logout on Idle',   'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'2fa',      'key'=>'authentication.two_factor',        'label'=>'Two-Factor Auth (2FA)', 'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'password', 'key'=>'authentication.password_policy',   'label'=>'Password Min Length',   'value'=>'8','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:6|max:32'],
            ['category'=>$c,'section'=>'password', 'key'=>'authentication.password_complexity','label'=>'Require Complex Password','value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'password', 'key'=>'authentication.password_expiry',   'label'=>'Password Expiry (days)','value'=>'0','type'=>'integer','input_type'=>'number','description'=>'0 = never expire'],
            ['category'=>$c,'section'=>'lockout',  'key'=>'authentication.login_attempt_limit','label'=>'Max Login Attempts',  'value'=>'5','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:20'],
            ['category'=>$c,'section'=>'lockout',  'key'=>'authentication.lockout_time',      'label'=>'Lockout Duration (min)','value'=>'30','type'=>'integer','input_type'=>'number'],
        ];
    }

    // =========================================================================
    // 5. LOCALIZATION
    // =========================================================================
    private function localizationSettings(): array
    {
        $c = SettingCategory::LOCALIZATION;
        return [
            ['category'=>$c,'section'=>'regional','key'=>'localization.language',         'label'=>'Language',               'value'=>'en',    'type'=>'string','input_type'=>'select','is_public'=>true,'options'=>[['label'=>'English','value'=>'en'],['label'=>'Amharic','value'=>'am'],['label'=>'Arabic','value'=>'ar'],['label'=>'French','value'=>'fr']]],
            ['category'=>$c,'section'=>'regional','key'=>'localization.timezone',         'label'=>'Timezone',               'value'=>'Africa/Addis_Ababa','type'=>'string','input_type'=>'select','is_public'=>true,'options'=>$this->timezoneOptions()],
            ['category'=>$c,'section'=>'regional','key'=>'localization.currency',         'label'=>'Currency',               'value'=>'ETB',   'type'=>'string','input_type'=>'select','is_public'=>true,'options'=>$this->currencyOptions()],
            ['category'=>$c,'section'=>'regional','key'=>'localization.date_format',      'label'=>'Date Format',            'value'=>'d/m/Y', 'type'=>'string','input_type'=>'select','is_public'=>true,'options'=>[['label'=>'DD/MM/YYYY','value'=>'d/m/Y'],['label'=>'MM/DD/YYYY','value'=>'m/d/Y'],['label'=>'YYYY-MM-DD','value'=>'Y-m-d']]],
            ['category'=>$c,'section'=>'regional','key'=>'localization.week_start',       'label'=>'First Day of Week',      'value'=>'1',     'type'=>'string','input_type'=>'select','options'=>[['label'=>'Sunday','value'=>'0'],['label'=>'Monday','value'=>'1'],['label'=>'Saturday','value'=>'6']]],
            ['category'=>$c,'section'=>'regional','key'=>'localization.rtl',              'label'=>'Right-to-Left (RTL)',    'value'=>'0',     'type'=>'boolean','input_type'=>'switch','is_public'=>true],
            ['category'=>$c,'section'=>'numbers', 'key'=>'localization.thousands_sep',    'label'=>'Thousands Separator',    'value'=>',',     'type'=>'string','input_type'=>'select','options'=>[['label'=>'Comma (1,000)','value'=>','],['label'=>'Period (1.000)','value'=>'.'],['label'=>'Space (1 000)','value'=>' ']]],
            ['category'=>$c,'section'=>'numbers', 'key'=>'localization.decimal_sep',      'label'=>'Decimal Separator',      'value'=>'.',     'type'=>'string','input_type'=>'select','options'=>[['label'=>'Period (1.50)','value'=>'.'],['label'=>'Comma (1,50)','value'=>',']]],
        ];
    }

    // =========================================================================
    // 6. EMAIL
    // =========================================================================
    private function emailSettings(): array
    {
        $c = SettingCategory::EMAIL;
        return [
            ['category'=>$c,'section'=>'general','key'=>'email.mail_driver',    'label'=>'Mail Driver',       'value'=>'smtp',           'type'=>'string','input_type'=>'select','options'=>[['label'=>'SMTP','value'=>'smtp'],['label'=>'Mailgun','value'=>'mailgun'],['label'=>'Log (testing)','value'=>'log'],['label'=>'Array (testing)','value'=>'array']]],
            ['category'=>$c,'section'=>'general','key'=>'email.sender_name',    'label'=>'Sender Name',       'value'=>'ERP System',     'type'=>'string','input_type'=>'text','validation_rules'=>'nullable|string|max:100'],
            ['category'=>$c,'section'=>'general','key'=>'email.sender_email',   'label'=>'Sender Email',      'value'=>'noreply@example.com','type'=>'string','input_type'=>'email','validation_rules'=>'nullable|email'],
            ['category'=>$c,'section'=>'smtp',   'key'=>'email.smtp_host',      'label'=>'SMTP Host',         'value'=>'smtp.mailtrap.io','type'=>'string','input_type'=>'text','depends_on'=>'email.mail_driver:smtp'],
            ['category'=>$c,'section'=>'smtp',   'key'=>'email.smtp_port',      'label'=>'SMTP Port',         'value'=>'587',            'type'=>'integer','input_type'=>'number','depends_on'=>'email.mail_driver:smtp','validation_rules'=>'nullable|integer|min:1|max:65535'],
            ['category'=>$c,'section'=>'smtp',   'key'=>'email.smtp_username',  'label'=>'SMTP Username',     'value'=>'',               'type'=>'string','input_type'=>'text','depends_on'=>'email.mail_driver:smtp'],
            ['category'=>$c,'section'=>'smtp',   'key'=>'email.smtp_password',  'label'=>'SMTP Password',     'value'=>'',               'type'=>'string','input_type'=>'password','depends_on'=>'email.mail_driver:smtp','is_sensitive'=>true],
            ['category'=>$c,'section'=>'smtp',   'key'=>'email.smtp_encryption','label'=>'Encryption',        'value'=>'tls',            'type'=>'string','input_type'=>'select','depends_on'=>'email.mail_driver:smtp','options'=>[['label'=>'TLS','value'=>'tls'],['label'=>'SSL','value'=>'ssl'],['label'=>'None','value'=>'']]],
        ];
    }

    // =========================================================================
    // 7. NOTIFICATIONS
    // =========================================================================
    private function notificationSettings(): array
    {
        $c = SettingCategory::NOTIFICATION;
        return [
            ['category'=>$c,'section'=>'channels','key'=>'notification.email_enabled',    'label'=>'Email Notifications',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'channels','key'=>'notification.sms_enabled',      'label'=>'SMS Notifications',     'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'channels','key'=>'notification.push_enabled',     'label'=>'Push Notifications',    'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'channels','key'=>'notification.slack_enabled',    'label'=>'Slack Notifications',   'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'channels','key'=>'notification.telegram_enabled', 'label'=>'Telegram Notifications','value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'channels','key'=>'notification.whatsapp_enabled', 'label'=>'WhatsApp Notifications','value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'slack',   'key'=>'notification.slack_webhook',    'label'=>'Slack Webhook URL',     'value'=>'','type'=>'string','input_type'=>'url','depends_on'=>'notification.slack_enabled:1'],
            ['category'=>$c,'section'=>'telegram','key'=>'notification.telegram_bot_token','label'=>'Telegram Bot Token',  'value'=>'','type'=>'string','input_type'=>'text','depends_on'=>'notification.telegram_enabled:1','is_sensitive'=>true],
            ['category'=>$c,'section'=>'telegram','key'=>'notification.telegram_chat_id', 'label'=>'Telegram Chat ID',     'value'=>'','type'=>'string','input_type'=>'text','depends_on'=>'notification.telegram_enabled:1'],
        ];
    }

    // =========================================================================
    // 8. STORAGE
    // =========================================================================
    private function storageSettings(): array
    {
        $c = SettingCategory::STORAGE;
        return [
            ['category'=>$c,'section'=>'general','key'=>'storage.driver',          'label'=>'Storage Driver',      'value'=>'local',  'type'=>'string','input_type'=>'select','options'=>[['label'=>'Local','value'=>'local'],['label'=>'Amazon S3','value'=>'s3'],['label'=>'Cloudinary','value'=>'cloudinary']]],
            ['category'=>$c,'section'=>'general','key'=>'storage.max_upload_size', 'label'=>'Max Upload Size (MB)','value'=>'10',     'type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:100'],
            ['category'=>$c,'section'=>'general','key'=>'storage.allowed_types',   'label'=>'Allowed File Types',  'value'=>'jpg,jpeg,png,gif,pdf,docx,xlsx,csv','type'=>'string','input_type'=>'text','description'=>'Comma-separated extensions'],
            ['category'=>$c,'section'=>'s3',     'key'=>'storage.s3_key',          'label'=>'AWS Access Key',      'value'=>'',       'type'=>'string','input_type'=>'text','depends_on'=>'storage.driver:s3','is_sensitive'=>true],
            ['category'=>$c,'section'=>'s3',     'key'=>'storage.s3_secret',       'label'=>'AWS Secret',          'value'=>'',       'type'=>'string','input_type'=>'password','depends_on'=>'storage.driver:s3','is_sensitive'=>true],
            ['category'=>$c,'section'=>'s3',     'key'=>'storage.s3_region',       'label'=>'AWS Region',          'value'=>'us-east-1','type'=>'string','input_type'=>'text','depends_on'=>'storage.driver:s3'],
            ['category'=>$c,'section'=>'s3',     'key'=>'storage.s3_bucket',       'label'=>'S3 Bucket',           'value'=>'',       'type'=>'string','input_type'=>'text','depends_on'=>'storage.driver:s3'],
        ];
    }

    // =========================================================================
    // 9. BACKUP
    // =========================================================================
    private function backupSettings(): array
    {
        $c = SettingCategory::BACKUP;
        return [
            ['category'=>$c,'section'=>'schedule','key'=>'backup.auto_backup',      'label'=>'Automatic Backups',    'value'=>'0',      'type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'schedule','key'=>'backup.backup_frequency', 'label'=>'Backup Frequency',     'value'=>'daily',  'type'=>'string','input_type'=>'select','depends_on'=>'backup.auto_backup:1','options'=>[['label'=>'Daily','value'=>'daily'],['label'=>'Weekly','value'=>'weekly'],['label'=>'Monthly','value'=>'monthly']]],
            ['category'=>$c,'section'=>'schedule','key'=>'backup.retention_days',   'label'=>'Keep Backups (days)',  'value'=>'30',     'type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:365'],
            ['category'=>$c,'section'=>'schedule','key'=>'backup.backup_time',      'label'=>'Backup Time',          'value'=>'02:00',  'type'=>'string','input_type'=>'text','depends_on'=>'backup.auto_backup:1'],
        ];
    }

    // =========================================================================
    // 10. SECURITY
    // =========================================================================
    private function securitySettings(): array
    {
        $c = SettingCategory::SECURITY;
        return [
            ['category'=>$c,'section'=>'access',   'key'=>'security.force_https',       'label'=>'Force HTTPS',          'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'access',   'key'=>'security.ip_whitelist',      'label'=>'IP Whitelist',          'value'=>'','type'=>'string','input_type'=>'textarea','description'=>'One IP per line. Leave empty to allow all.'],
            ['category'=>$c,'section'=>'access',   'key'=>'security.ip_blacklist',      'label'=>'IP Blacklist',          'value'=>'','type'=>'string','input_type'=>'textarea','description'=>'One IP per line.'],
            ['category'=>$c,'section'=>'rate',     'key'=>'security.rate_limiting',     'label'=>'Rate Limiting',         'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'rate',     'key'=>'security.rate_limit_max',    'label'=>'Max Requests/Minute',   'value'=>'60','type'=>'integer','input_type'=>'number','depends_on'=>'security.rate_limiting:1'],
            ['category'=>$c,'section'=>'headers',  'key'=>'security.security_headers',  'label'=>'Security Headers',      'value'=>'1','type'=>'boolean','input_type'=>'switch','description'=>'X-Frame-Options, X-XSS-Protection, etc.'],
            ['category'=>$c,'section'=>'audit',    'key'=>'security.audit_log_enabled', 'label'=>'Audit Logging',         'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'maintenance','key'=>'security.maintenance_mode','label'=>'Maintenance Mode',      'value'=>'0','type'=>'boolean','input_type'=>'switch','description'=>'When enabled, only admins can access the site'],
            ['category'=>$c,'section'=>'maintenance','key'=>'security.maintenance_message','label'=>'Maintenance Message','value'=>'We are performing scheduled maintenance. Be right back!','type'=>'string','input_type'=>'textarea','depends_on'=>'security.maintenance_mode:1'],
        ];
    }

    // =========================================================================
    // 11. MODULES
    // =========================================================================
    private function modulesSettings(): array
    {
        $c = SettingCategory::MODULES;
        $modules = [
            'hr'          => ['label'=>'Human Resources',   'icon'=>'fa-users',           'order'=>1],
            'payroll'     => ['label'=>'Payroll',           'icon'=>'fa-money-bill-wave',  'order'=>2],
            'crm'         => ['label'=>'CRM',               'icon'=>'fa-handshake',        'order'=>3],
            'inventory'   => ['label'=>'Inventory',         'icon'=>'fa-boxes-stacked',    'order'=>4],
            'accounting'  => ['label'=>'Accounting',        'icon'=>'fa-calculator',       'order'=>5],
            'attendance'  => ['label'=>'Attendance',        'icon'=>'fa-clock',            'order'=>6],
            'recruitment' => ['label'=>'Recruitment',       'icon'=>'fa-person-walking',   'order'=>7],
            'performance' => ['label'=>'Performance',       'icon'=>'fa-chart-line',       'order'=>8],
            'training'    => ['label'=>'Training',          'icon'=>'fa-graduation-cap',   'order'=>9],
            'assets'      => ['label'=>'Asset Management',  'icon'=>'fa-laptop',           'order'=>10],
            'projects'    => ['label'=>'Projects',          'icon'=>'fa-diagram-project',  'order'=>11],
            'helpdesk'    => ['label'=>'Help Desk',         'icon'=>'fa-headset',          'order'=>12],
        ];

        $settings = [];
        foreach ($modules as $slug => $meta) {
            $settings[] = ['category'=>$c,'section'=>$slug,'key'=>"module.{$slug}.enabled",    'label'=>$meta['label'].' — Enabled','value'=>'1','type'=>'boolean','input_type'=>'switch','is_public'=>true];
            $settings[] = ['category'=>$c,'section'=>$slug,'key'=>"module.{$slug}.menu_label",'label'=>$meta['label'].' — Menu Label','value'=>$meta['label'],'type'=>'string','input_type'=>'text'];
            $settings[] = ['category'=>$c,'section'=>$slug,'key'=>"module.{$slug}.icon",       'label'=>$meta['label'].' — Icon','value'=>$meta['icon'],'type'=>'string','input_type'=>'text'];
            $settings[] = ['category'=>$c,'section'=>$slug,'key'=>"module.{$slug}.sort_order", 'label'=>$meta['label'].' — Order','value'=>(string)$meta['order'],'type'=>'integer','input_type'=>'number'];
        }
        return $settings;
    }

    // =========================================================================
    // 12. INTEGRATIONS
    // =========================================================================
    private function integrationSettings(): array
    {
        $c = SettingCategory::INTEGRATION;
        return [
            // Google
            ['category'=>$c,'section'=>'google',   'key'=>'integration.google_client_id',     'label'=>'Google Client ID',    'value'=>'','type'=>'string','input_type'=>'text','is_sensitive'=>false],
            ['category'=>$c,'section'=>'google',   'key'=>'integration.google_client_secret', 'label'=>'Google Client Secret','value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
            ['category'=>$c,'section'=>'google',   'key'=>'integration.google_maps_key',      'label'=>'Google Maps API Key', 'value'=>'','type'=>'string','input_type'=>'text','is_sensitive'=>true],
            // Microsoft / Azure
            ['category'=>$c,'section'=>'microsoft','key'=>'integration.microsoft_client_id',  'label'=>'Microsoft Client ID', 'value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'microsoft','key'=>'integration.microsoft_client_secret','label'=>'Microsoft Secret', 'value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
            // Stripe
            ['category'=>$c,'section'=>'stripe',   'key'=>'integration.stripe_key',           'label'=>'Stripe Publishable Key','value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'stripe',   'key'=>'integration.stripe_secret',        'label'=>'Stripe Secret Key',   'value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
            // PayPal
            ['category'=>$c,'section'=>'paypal',   'key'=>'integration.paypal_client_id',     'label'=>'PayPal Client ID',    'value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'paypal',   'key'=>'integration.paypal_client_secret', 'label'=>'PayPal Secret',       'value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
            ['category'=>$c,'section'=>'paypal',   'key'=>'integration.paypal_mode',          'label'=>'PayPal Mode',         'value'=>'sandbox','type'=>'string','input_type'=>'select','options'=>[['label'=>'Sandbox','value'=>'sandbox'],['label'=>'Live','value'=>'live']]],
            // Telebirr
            ['category'=>$c,'section'=>'telebirr', 'key'=>'integration.telebirr_app_id',      'label'=>'Telebirr App ID',     'value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'telebirr', 'key'=>'integration.telebirr_app_key',     'label'=>'Telebirr App Key',    'value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
            // Pusher
            ['category'=>$c,'section'=>'pusher',   'key'=>'integration.pusher_app_id',        'label'=>'Pusher App ID',       'value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'pusher',   'key'=>'integration.pusher_key',           'label'=>'Pusher Key',          'value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'pusher',   'key'=>'integration.pusher_secret',        'label'=>'Pusher Secret',       'value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
            ['category'=>$c,'section'=>'pusher',   'key'=>'integration.pusher_cluster',       'label'=>'Pusher Cluster',      'value'=>'mt1','type'=>'string','input_type'=>'text'],
            // Zoom
            ['category'=>$c,'section'=>'zoom',     'key'=>'integration.zoom_api_key',         'label'=>'Zoom API Key',        'value'=>'','type'=>'string','input_type'=>'text','is_sensitive'=>true],
            ['category'=>$c,'section'=>'zoom',     'key'=>'integration.zoom_api_secret',      'label'=>'Zoom API Secret',     'value'=>'','type'=>'string','input_type'=>'password','is_sensitive'=>true],
        ];
    }

    // =========================================================================
    // 13. MAINTENANCE
    // =========================================================================
    private function maintenanceSettings(): array
    {
        $c = SettingCategory::MAINTENANCE;
        return [
            ['category'=>$c,'section'=>'queue','key'=>'maintenance.queue_driver',   'label'=>'Queue Driver','value'=>'sync','type'=>'string','input_type'=>'select','options'=>[['label'=>'Sync','value'=>'sync'],['label'=>'Database','value'=>'database'],['label'=>'Redis','value'=>'redis']]],
        ];
    }

    // =========================================================================
    // 14. LICENSE
    // =========================================================================
    private function licenseSettings(): array
    {
        $c = SettingCategory::LICENSE;
        return [
            ['category'=>$c,'section'=>'license','key'=>'license.key',             'label'=>'License Key',      'value'=>'','type'=>'string','input_type'=>'text','is_sensitive'=>true],
            ['category'=>$c,'section'=>'license','key'=>'license.support_expiry',  'label'=>'Support Expiry',   'value'=>'','type'=>'string','input_type'=>'text'],
            ['category'=>$c,'section'=>'license','key'=>'license.update_channel',  'label'=>'Update Channel',   'value'=>'stable','type'=>'string','input_type'=>'select','options'=>[['label'=>'Stable','value'=>'stable'],['label'=>'Beta','value'=>'beta']]],
            ['category'=>$c,'section'=>'license','key'=>'license.status',          'label'=>'License Status',   'value'=>'active','type'=>'string','input_type'=>'text','is_editable'=>false],
        ];
    }

    // =========================================================================
    // 15. WHITE LABEL
    // =========================================================================
    private function whitelabelSettings(): array
    {
        $c = SettingCategory::WHITELABEL;
        return [
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.app_name',          'label'=>'System Name',        'value'=>'ERP System','type'=>'string','input_type'=>'text','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.short_name',        'label'=>'Short Name',         'value'=>'ERP',       'type'=>'string','input_type'=>'text','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.browser_title',     'label'=>'Browser Title',      'value'=>'ERP - Ultimate Business Suite','type'=>'string','input_type'=>'text','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.logo',              'label'=>'Company Logo (Light)','value'=>null,       'type'=>'image', 'input_type'=>'image','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.logo_dark',         'label'=>'Company Logo (Dark)','value'=>null,        'type'=>'image', 'input_type'=>'image','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.favicon',           'label'=>'Favicon',            'value'=>null,        'type'=>'image', 'input_type'=>'image','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.welcome_message',   'label'=>'Login Welcome Message','value'=>'Welcome Back! Please enter your details below.','type'=>'string','input_type'=>'text','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.footer_text',       'label'=>'Footer Text',        'value'=>'All rights reserved.','type'=>'string','input_type'=>'text','is_public'=>true],
            ['category'=>$c,'section'=>'branding','key'=>'whitelabel.copyright',         'label'=>'Copyright Text',     'value'=>'Copyright &copy; '.date('Y').' ERP.','type'=>'string','input_type'=>'text','is_public'=>true],
            
            // Loading screen
            ['category'=>$c,'section'=>'loader',  'key'=>'whitelabel.loading_screen',    'label'=>'Show Loading Screen','value'=>'1',         'type'=>'boolean','input_type'=>'switch','is_public'=>true],
            ['category'=>$c,'section'=>'loader',  'key'=>'whitelabel.loading_animation', 'label'=>'Loading Animation',  'value'=>'ellipse',   'type'=>'string','input_type'=>'select','options'=>[['label'=>'Elipsis Dot','value'=>'ellipse'],['label'=>'Spinner Ring','value'=>'spinner'],['label'=>'Double bounce','value'=>'double-bounce']],'is_public'=>true],

            // Login templates
            ['category'=>$c,'section'=>'login',   'key'=>'whitelabel.login_logo',        'label'=>'Login Logo',         'value'=>null,        'type'=>'image', 'input_type'=>'image','is_public'=>true],
            ['category'=>$c,'section'=>'login',   'key'=>'whitelabel.login_background',  'label'=>'Login Background Banner','value'=>null,   'type'=>'image', 'input_type'=>'image','is_public'=>true],
            
            // Custom pages
            ['category'=>$c,'section'=>'pages',   'key'=>'whitelabel.404_logo',          'label'=>'404 Page Logo',      'value'=>null,        'type'=>'image', 'input_type'=>'image'],
            ['category'=>$c,'section'=>'pages',   'key'=>'whitelabel.maintenance_logo',  'label'=>'Maintenance Page Logo','value'=>null,     'type'=>'image', 'input_type'=>'image'],
            
            // Documents
            ['category'=>$c,'section'=>'print',   'key'=>'whitelabel.pdf_logo',          'label'=>'PDF Document Logo',  'value'=>null,        'type'=>'image', 'input_type'=>'image'],
            ['category'=>$c,'section'=>'print',   'key'=>'whitelabel.invoice_logo',      'label'=>'Invoice Logo',       'value'=>null,        'type'=>'image', 'input_type'=>'image'],
            ['category'=>$c,'section'=>'print',   'key'=>'whitelabel.email_logo',        'label'=>'Email Header Logo',  'value'=>null,        'type'=>'image', 'input_type'=>'image'],
            ['category'=>$c,'section'=>'print',   'key'=>'whitelabel.pdf_header',        'label'=>'PDF Header text',    'value'=>'',          'type'=>'string','input_type'=>'textarea'],
            ['category'=>$c,'section'=>'print',   'key'=>'whitelabel.pdf_footer',        'label'=>'PDF Footer text',    'value'=>'',          'type'=>'string','input_type'=>'textarea'],
            
            // Dashboard
            ['category'=>$c,'section'=>'dashboard','key'=>'whitelabel.dashboard_welcome_banner','label'=>'Dashboard Welcome Banner Text','value'=>'Explore analytics, workflows, and operations.','type'=>'string','input_type'=>'text','is_public'=>true],
        ];
    }

    // =========================================================================
    // 14. LOGS
    // =========================================================================
    private function logsSettings(): array
    {
        $c = SettingCategory::LOGS;
        return [
            ['category'=>$c,'section'=>'general','key'=>'logs.enabled',                'label'=>'Enable Logging',        'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'general','key'=>'logs.level',                  'label'=>'Log Level',             'value'=>'debug','type'=>'string','input_type'=>'select','options'=>[['label'=>'Debug','value'=>'debug'],['label'=>'Info','value'=>'info'],['label'=>'Notice','value'=>'notice'],['label'=>'Warning','value'=>'warning'],['label'=>'Error','value'=>'error'],['label'=>'Critical','value'=>'critical']]],
            ['category'=>$c,'section'=>'general','key'=>'logs.channel',                'label'=>'Default Log Channel',   'value'=>'stack','type'=>'string','input_type'=>'select','options'=>[['label'=>'Stack (Multiple)','value'=>'stack'],['label'=>'Single File','value'=>'single'],['label'=>'Daily Files','value'=>'daily'],['label'=>'Syslog','value'=>'syslog']]],
            ['category'=>$c,'section'=>'rotation','key'=>'logs.max_files',             'label'=>'Max Log Files',         'value'=>'14','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:365','description'=>'For daily log channel'],
            ['category'=>$c,'section'=>'rotation','key'=>'logs.max_file_size',         'label'=>'Max File Size (MB)',    'value'=>'10','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:100'],
            ['category'=>$c,'section'=>'audit',  'key'=>'logs.audit_enabled',          'label'=>'Enable Audit Logs',     'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'audit',  'key'=>'logs.audit_retention_days',   'label'=>'Audit Retention (days)','value'=>'90','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:3650'],
            ['category'=>$c,'section'=>'audit',  'key'=>'logs.log_queries',            'label'=>'Log Database Queries',  'value'=>'0','type'=>'boolean','input_type'=>'switch','description'=>'Warning: Can generate large logs'],
            ['category'=>$c,'section'=>'audit',  'key'=>'logs.log_slow_queries',       'label'=>'Log Slow Queries (ms)', 'value'=>'1000','type'=>'integer','input_type'=>'number','description'=>'Log queries slower than this threshold'],
            ['category'=>$c,'section'=>'errors', 'key'=>'logs.error_reporting',        'label'=>'Error Reporting',       'value'=>'all','type'=>'string','input_type'=>'select','options'=>[['label'=>'All','value'=>'all'],['label'=>'Errors & Warnings','value'=>'errors'],['label'=>'Errors Only','value'=>'errors_only'],['label'=>'None','value'=>'none']]],
            ['category'=>$c,'section'=>'errors', 'key'=>'logs.display_errors',         'label'=>'Display Errors',        'value'=>'0','type'=>'boolean','input_type'=>'switch','description'=>'Should be disabled in production'],
        ];
    }

    // =========================================================================
    // 16. ADVANCED
    // =========================================================================
    private function advancedSettings(): array
    {
        $c = SettingCategory::ADVANCED;
        return [
            ['category'=>$c,'section'=>'system','key'=>'advanced.debug_mode',          'label'=>'Debug Mode',            'value'=>'0','type'=>'boolean','input_type'=>'switch','description'=>'Should be disabled in production'],
            ['category'=>$c,'section'=>'system','key'=>'advanced.app_env',             'label'=>'Application Environment','value'=>'production','type'=>'string','input_type'=>'select','options'=>[['label'=>'Production','value'=>'production'],['label'=>'Staging','value'=>'staging'],['label'=>'Development','value'=>'development'],['label'=>'Local','value'=>'local']]],
            ['category'=>$c,'section'=>'system','key'=>'advanced.system_info',         'label'=>'Show System Info',      'value'=>'0','type'=>'boolean','input_type'=>'switch','description'=>'Display PHP/server info to admins'],
            ['category'=>$c,'section'=>'performance','key'=>'advanced.cache_enabled',  'label'=>'Enable Caching',        'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'performance','key'=>'advanced.cache_lifetime', 'label'=>'Cache Lifetime (min)',  'value'=>'60','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:1440'],
            ['category'=>$c,'section'=>'performance','key'=>'advanced.minify_html',    'label'=>'Minify HTML',           'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'performance','key'=>'advanced.minify_css',     'label'=>'Minify CSS',            'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'performance','key'=>'advanced.minify_js',      'label'=>'Minify JavaScript',     'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'performance','key'=>'advanced.lazy_loading',   'label'=>'Lazy Load Images',      'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'api',     'key'=>'advanced.api_enabled',       'label'=>'Enable REST API',       'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'api',     'key'=>'advanced.api_rate_limit',    'label'=>'API Rate Limit',        'value'=>'60','type'=>'integer','input_type'=>'number','description'=>'Requests per minute per IP'],
            ['category'=>$c,'section'=>'api',     'key'=>'advanced.api_throttle',      'label'=>'API Throttle (sec)',    'value'=>'1','type'=>'integer','input_type'=>'number','description'=>'Minimum seconds between requests'],
            ['category'=>$c,'section'=>'developer','key'=>'advanced.telescope_enabled','label'=>'Enable Laravel Telescope','value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'developer','key'=>'advanced.query_log',        'label'=>'Query Logging',         'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'developer','key'=>'advanced.route_cache',      'label'=>'Route Caching',         'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'developer','key'=>'advanced.config_cache',     'label'=>'Config Caching',        'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'developer','key'=>'advanced.view_cache',       'label'=>'View Caching',          'value'=>'1','type'=>'boolean','input_type'=>'switch'],
        ];
    }

    // =========================================================================
    // 17. DASHBOARD
    // =========================================================================
    private function dashboardSettings(): array
    {
        $c = SettingCategory::DASHBOARD;
        return [
            ['category'=>$c,'section'=>'layout','key'=>'dashboard.layout',             'label'=>'Dashboard Layout',      'value'=>'grid','type'=>'string','input_type'=>'select','options'=>[['label'=>'Grid Layout','value'=>'grid'],['label'=>'Masonry Layout','value'=>'masonry'],['label'=>'List Layout','value'=>'list']]],
            ['category'=>$c,'section'=>'layout','key'=>'dashboard.columns',            'label'=>'Grid Columns',          'value'=>'3','type'=>'integer','input_type'=>'select','options'=>[['label'=>'2 Columns','value'=>'2'],['label'=>'3 Columns','value'=>'3'],['label'=>'4 Columns','value'=>'4']]],
            ['category'=>$c,'section'=>'widgets','key'=>'dashboard.widgets_enabled',   'label'=>'Enable Widgets',        'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'widgets','key'=>'dashboard.draggable_widgets', 'label'=>'Draggable Widgets',     'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'widgets','key'=>'dashboard.widget_refresh',    'label'=>'Auto Refresh (sec)',    'value'=>'300','type'=>'integer','input_type'=>'number','description'=>'0 = disabled'],
            ['category'=>$c,'section'=>'widgets','key'=>'dashboard.default_widgets',   'label'=>'Default Widgets',       'value'=>'stats,recent_activity,charts','type'=>'string','input_type'=>'text','description'=>'Comma-separated widget IDs'],
            ['category'=>$c,'section'=>'display','key'=>'dashboard.show_welcome',      'label'=>'Show Welcome Banner',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'display','key'=>'dashboard.show_quick_actions','label'=>'Show Quick Actions',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'display','key'=>'dashboard.show_notifications','label'=>'Show Notifications',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'display','key'=>'dashboard.items_per_widget',  'label'=>'Items Per Widget',      'value'=>'5','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:20'],
        ];
    }

    // =========================================================================
    // 18. MENU
    // =========================================================================
    private function menuSettings(): array
    {
        $c = SettingCategory::MENU;
        return [
            ['category'=>$c,'section'=>'layout','key'=>'menu.position',                'label'=>'Menu Position',         'value'=>'left','type'=>'string','input_type'=>'select','options'=>[['label'=>'Left Sidebar','value'=>'left'],['label'=>'Right Sidebar','value'=>'right'],['label'=>'Top Horizontal','value'=>'top']],'is_public'=>true],
            ['category'=>$c,'section'=>'layout','key'=>'menu.collapsible',             'label'=>'Collapsible Menu',      'value'=>'1','type'=>'boolean','input_type'=>'switch','is_public'=>true],
            ['category'=>$c,'section'=>'layout','key'=>'menu.auto_collapse',           'label'=>'Auto Collapse Submenu', 'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'layout','key'=>'menu.remember_state',          'label'=>'Remember Menu State',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'style',  'key'=>'menu.icon_style',             'label'=>'Icon Style',            'value'=>'solid','type'=>'string','input_type'=>'select','options'=>[['label'=>'Solid','value'=>'solid'],['label'=>'Regular','value'=>'regular'],['label'=>'Light','value'=>'light']]],
            ['category'=>$c,'section'=>'style',  'key'=>'menu.show_icons',             'label'=>'Show Menu Icons',       'value'=>'1','type'=>'boolean','input_type'=>'switch','is_public'=>true],
            ['category'=>$c,'section'=>'style',  'key'=>'menu.show_badges',            'label'=>'Show Badges/Counts',    'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'style',  'key'=>'menu.compact_mode',           'label'=>'Compact Menu',          'value'=>'0','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'builder','key'=>'menu.builder_enabled',        'label'=>'Enable Menu Builder',   'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'builder','key'=>'menu.max_depth',              'label'=>'Max Nesting Depth',     'value'=>'3','type'=>'integer','input_type'=>'number','validation_rules'=>'nullable|integer|min:1|max:5'],
            ['category'=>$c,'section'=>'search', 'key'=>'menu.search_enabled',         'label'=>'Enable Menu Search',    'value'=>'1','type'=>'boolean','input_type'=>'switch'],
            ['category'=>$c,'section'=>'search', 'key'=>'menu.search_placeholder',     'label'=>'Search Placeholder',    'value'=>'Search menu...','type'=>'string','input_type'=>'text'],
        ];
    }

    // =========================================================================
    // Helpers
    // =========================================================================
    private function timezoneOptions(): array
    {
        $zones = ['UTC','Africa/Addis_Ababa','Africa/Nairobi','Africa/Cairo','Africa/Lagos','Africa/Johannesburg','Europe/London','Europe/Paris','Europe/Berlin','Asia/Dubai','Asia/Riyadh','Asia/Kolkata','Asia/Singapore','America/New_York','America/Chicago','America/Los_Angeles','America/Sao_Paulo','Australia/Sydney'];
        return array_map(fn($z) => ['label'=>$z,'value'=>$z], $zones);
    }

    private function currencyOptions(): array
    {
        $currencies = ['ETB'=>'Ethiopian Birr (ETB)','USD'=>'US Dollar (USD)','EUR'=>'Euro (EUR)','GBP'=>'British Pound (GBP)','AED'=>'UAE Dirham (AED)','SAR'=>'Saudi Riyal (SAR)','KES'=>'Kenyan Shilling (KES)','NGN'=>'Nigerian Naira (NGN)','ZAR'=>'South African Rand (ZAR)','INR'=>'Indian Rupee (INR)'];
        return array_map(fn($v,$k) => ['label'=>$v,'value'=>$k], $currencies, array_keys($currencies));
    }
}
