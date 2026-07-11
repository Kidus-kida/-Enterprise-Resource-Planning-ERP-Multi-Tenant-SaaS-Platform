<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Superadmin\Facades\Setting;
use Modules\Superadmin\Models\SettingsAuditLog;
use Modules\Superadmin\Models\SystemSetting;
use Modules\Superadmin\Services\SettingsService;
use Modules\Superadmin\Settings\SettingCategory;

class SuperadminSettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    // =========================================================================
    // Index — redirect to general section
    // =========================================================================

    public function index(): RedirectResponse
    {
        return redirect()->route('superadmin.settings.show', SettingCategory::GENERAL);
    }

    // =========================================================================
    // Show — render a section view
    // =========================================================================

    public function show(string $section)
    {
        $category = $this->resolveCategory($section);

        if (!$category) {
            abort(404, 'Settings section not found.');
        }

        $settings = $this->settings->getCategorySettings($category)
            ->groupBy('section');

        $allSettings = $this->settings->all();

        // Use 'advanced' view matching the route parameter for the whitelabel DB category
        $viewName = $section === 'advanced' ? 'advanced' : $category;

        return view("superadmin::settings.{$viewName}", compact('settings', 'allSettings', 'category', 'section'));
    }

    // =========================================================================
    // Update — save a settings section
    // =========================================================================

    public function update(Request $request, string $section): JsonResponse|RedirectResponse
    {
        $category = $this->resolveCategory($section);

        if (!$category) {
            abort(404, 'Settings section not found.');
        }

        // Build dynamic validation rules from the DB definitions
        $dbSettings   = $this->settings->getCategorySettings($category);
        $rules        = [];
        $validKeys    = [];

        foreach ($dbSettings as $setting) {
            if (!$setting->is_editable) continue;
            $validKeys[] = $setting->key;
            if ($setting->validation_rules) {
                // Use last segment as form field name
                $fieldName = str_replace('.', '_', $setting->key);
                $rules[$fieldName] = $setting->validation_rules;
            }
        }

        // Run validation
        if (!empty($rules)) {
            $request->validate($rules);
        }

        // Map submitted form fields back to dot-notation keys and save
        $data = [];
        foreach ($validKeys as $key) {
            $fieldName = str_replace('.', '_', $key);
            if ($request->has($fieldName)) {
                $data[$key] = $request->input($fieldName);
            } elseif ($request->hasFile($fieldName)) {
                // handled separately via uploadMedia
            } else {
                // Unchecked checkboxes / toggles default to '0'
                $setting = SystemSetting::where('key', $key)->first();
                if ($setting && $setting->type === 'boolean') {
                    $data[$key] = '0';
                }
            }
        }

        $this->settings->setMany($data);

        $label = SettingCategory::LABELS[$category] ?? SettingCategory::LABELS[$section] ?? ucfirst($section);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $label . ' saved successfully.',
            ]);
        }

        return redirect()
            ->route('superadmin.settings.show', $section)
            ->with('success', $label . ' saved successfully.');
    }

    // =========================================================================
    // Media Upload
    // =========================================================================

    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate([
            'file'    => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg,ico|max:2048',
            'key'     => 'required|string',
        ]);

        $key     = $request->input('key');
        $setting = SystemSetting::where('key', $key)->first();

        if (!$setting || $setting->input_type !== 'image') {
            return response()->json(['success' => false, 'message' => 'Invalid media key.'], 422);
        }

        // Delete old file if exists
        if ($setting->value && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
        }

        $category  = $setting->category;
        $path      = $request->file('file')->store("settings/{$category}", 'public');

        $this->settings->set($key, $path);

        return response()->json([
            'success' => true,
            'path'    => $path,
            'url'     => Storage::url($path),
            'message' => 'File uploaded successfully.',
        ]);
    }

    // =========================================================================
    // Export Settings (JSON download)
    // =========================================================================

    public function export(): Response
    {
        $data = $this->settings->export();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="erp-settings-' . now()->format('Y-m-d') . '.json"',
        ]);
    }

    // =========================================================================
    // Import Settings (JSON upload)
    // =========================================================================

    public function import(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json|max:512',
        ]);

        $json = file_get_contents($request->file('settings_file')->getRealPath());
        $data = json_decode($json, true);

        if (!is_array($data)) {
            $message = 'Invalid JSON file format.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $result = $this->settings->import($data);

        $message = 'Import complete. ' . count($result['updated']) . ' settings updated, ' .
                   count($result['skipped']) . ' skipped.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'result' => $result]);
        }

        return redirect()->route('superadmin.settings.show', SettingCategory::GENERAL)
            ->with('success', $message);
    }

    // =========================================================================
    // Restore Defaults
    // =========================================================================

    public function restoreDefaults(Request $request, string $section): JsonResponse|RedirectResponse
    {
        $category = $this->resolveCategory($section);

        if (!$category) {
            abort(404);
        }

        $count = $this->settings->restoreDefaults($category);

        $message = "Restored {$count} settings to defaults.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('superadmin.settings.show', $section)
            ->with('success', $message);
    }

    // =========================================================================
    // Send Test Email
    // =========================================================================

    public function testEmail(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
        ]);

        try {
            // Apply SMTP settings from database at runtime
            $this->applyMailConfig();

            Mail::raw('This is a test email from your ERP System. SMTP is configured correctly!', function ($msg) use ($request) {
                $msg->to($request->input('to'))
                    ->subject('ERP Test Email — ' . setting('general.system_name', 'ERP System'));
            });

            return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // Clear Cache
    // =========================================================================

    public function clearCache(Request $request): JsonResponse
    {
        try {
            $this->settings->clearCache();
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            return response()->json(['success' => true, 'message' => 'All caches cleared successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // Settings Search (AJAX)
    // =========================================================================

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $q = trim($request->input('q'));

        $results = collect();

        // ── 1. Settings ────────────────────────────────────────────────────
        $settingsHits = $this->settings->search($q)->map(fn($s) => [
            'type'        => 'setting',
            'type_label'  => 'Setting',
            'type_icon'   => 'fa-solid fa-gear',
            'key'         => $s->key,
            'label'       => $s->label,
            'description' => $s->description ?? $s->category . ' › ' . $s->section,
            'url'         => route('superadmin.settings.show', $s->category),
        ]);
        $results = $results->merge($settingsHits);

        // ── 2. Users ───────────────────────────────────────────────────────
        try {
            $userHits = \App\User::where(function ($query) use ($q) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('email', 'LIKE', "%{$q}%")
                          ->orWhere('username', 'LIKE', "%{$q}%");
                })
                ->limit(5)
                ->get()
                ->map(fn($u) => [
                    'type'        => 'user',
                    'type_label'  => 'User',
                    'type_icon'   => 'fa-solid fa-user',
                    'key'         => 'user.' . $u->id,
                    'label'       => $u->name,
                    'description' => $u->email,
                    'url'         => route('employees.index'),
                ]);
            $results = $results->merge($userHits);
        } catch (\Throwable) {}

        // ── 3. Roles ───────────────────────────────────────────────────────
        try {
            $roleHits = \Spatie\Permission\Models\Role::where('name', 'LIKE', "%{$q}%")
                ->limit(5)
                ->get()
                ->map(fn($r) => [
                    'type'        => 'role',
                    'type_label'  => 'Role',
                    'type_icon'   => 'fa-solid fa-shield-halved',
                    'key'         => 'role.' . $r->id,
                    'label'       => $r->name,
                    'description' => 'Guard: ' . $r->guard_name,
                    'url'         => route('roles.index'),
                ]);
            $results = $results->merge($roleHits);
        } catch (\Throwable) {}

        // ── 4. Modules ─────────────────────────────────────────────────────
        try {
            $allModules = app(\Nwidart\Modules\Contracts\RepositoryInterface::class)->all();
            $moduleHits = collect($allModules)
                ->filter(fn($m) => str_contains(strtolower($m->getName()), strtolower($q)))
                ->take(5)
                ->map(fn($m) => [
                    'type'        => 'module',
                    'type_label'  => 'Module',
                    'type_icon'   => 'fa-solid fa-cubes',
                    'key'         => 'module.' . $m->getName(),
                    'label'       => $m->getName(),
                    'description' => $m->isEnabled() ? 'Active' : 'Disabled',
                    'url'         => route('superadmin.modules.index'),
                ]);
            $results = $results->merge($moduleHits);
        } catch (\Throwable) {}

        // ── 5. Menu Items (from custom structure) ──────────────────────────
        try {
            $menuJson = setting('menu.structure');
            if ($menuJson) {
                $menuItems = json_decode($menuJson, true) ?? [];
                $flat = [];
                array_walk_recursive($menuItems, function($v, $k) use (&$flat, $menuItems) {});
                // Flatten children
                $flatten = function($items) use (&$flatten) {
                    $result = [];
                    foreach ($items as $item) {
                        $result[] = $item;
                        if (!empty($item['children'])) {
                            $result = array_merge($result, $flatten($item['children']));
                        }
                    }
                    return $result;
                };
                $flat      = $flatten($menuItems);
                $menuHits  = collect($flat)
                    ->filter(fn($item) => isset($item['label']) && str_contains(strtolower($item['label']), strtolower($q)))
                    ->take(5)
                    ->map(fn($item) => [
                        'type'        => 'menu',
                        'type_label'  => 'Menu Item',
                        'type_icon'   => 'fa-solid fa-bars',
                        'key'         => 'menu.' . ($item['id'] ?? $item['label']),
                        'label'       => $item['label'],
                        'description' => 'Route: ' . ($item['route'] ?? '#'),
                        'url'         => route('superadmin.settings.show', 'menu'),
                    ]);
                $results = $results->merge($menuHits);
            }
        } catch (\Throwable) {}

        // ── 6. Artisan Commands ────────────────────────────────────────────
        $allowedCommands = ['cache:clear', 'config:clear', 'route:clear', 'view:clear', 'queue:restart', 'optimize'];
        $commandHits = collect($allowedCommands)
            ->filter(fn($cmd) => str_contains($cmd, strtolower($q)))
            ->map(fn($cmd) => [
                'type'        => 'command',
                'type_label'  => 'Artisan',
                'type_icon'   => 'fa-solid fa-terminal',
                'key'         => 'artisan.' . $cmd,
                'label'       => $cmd,
                'description' => 'Run from Maintenance',
                'url'         => route('superadmin.settings.show', 'maintenance'),
            ]);
        $results = $results->merge($commandHits);

        return response()->json(['results' => $results->values()]);
    }

    // =========================================================================
    // Audit Logs (paginated)
    // =========================================================================

    public function auditLogs(Request $request): \Illuminate\View\View
    {
        $query = SettingsAuditLog::with('user')->latest('changed_at');

        if ($request->filled('key')) {
            $query->forKey($request->input('key'));
        }
        if ($request->filled('user_id')) {
            $query->byUser($request->input('user_id'));
        }

        $logs    = $query->paginate(50);
        $section = SettingCategory::LOGS;
        $category = SettingCategory::LOGS;

        return view('superadmin::settings.logs', compact('logs', 'section', 'category'));
    }

    // =========================================================================
    // Maintenance Actions
    // =========================================================================

    public function runArtisan(Request $request): JsonResponse
    {
        $allowed = [
            'cache:clear'   => 'Cache cleared.',
            'config:clear'  => 'Config cache cleared.',
            'route:clear'   => 'Route cache cleared.',
            'view:clear'    => 'View cache cleared.',
            'queue:restart' => 'Queue workers restarted.',
            'optimize'      => 'Application optimized.',
        ];

        $command = $request->input('command');

        if (!array_key_exists($command, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Command not allowed.'], 422);
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();
            return response()->json(['success' => true, 'message' => $allowed[$command], 'output' => trim($output)]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    // =========================================================================
    // Menu Builder Save
    // =========================================================================

    public function saveMenu(Request $request): JsonResponse
    {
        $request->validate(['structure' => 'nullable|array']);
        $json = json_encode($request->input('structure', []));
        $this->settings->set('menu.structure', $json);
        Cache::forget('settings_all');
        return response()->json(['success' => true, 'message' => 'Menu structure saved.']);
    }

    // =========================================================================
    // Dashboard Builder Save
    // =========================================================================

    public function saveDashboard(Request $request): JsonResponse
    {
        $request->validate(['widgets' => 'nullable|array']);
        $json = json_encode($request->input('widgets', []));
        $this->settings->set('dashboard.widgets', $json);
        Cache::forget('settings_all');
        return response()->json(['success' => true, 'message' => 'Dashboard layout saved.']);
    }

    private function resolveCategory(string $section): ?string
    {
        // Map section slug aliases
        $aliases = [
            'advanced'    => SettingCategory::ADVANCED,
        ];
        if (isset($aliases[$section])) {
            return $aliases[$section];
        }

        // Allow using category name directly
        if (in_array($section, SettingCategory::ALL)) {
            return $section;
        }
        return null;
    }

    private function applyMailConfig(): void
    {
        config([
            'mail.mailers.smtp.host'       => setting('email.smtp_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port'       => setting('email.smtp_port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.username'   => setting('email.smtp_username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password'   => setting('email.smtp_password', config('mail.mailers.smtp.password')),
            'mail.mailers.smtp.encryption' => setting('email.smtp_encryption', config('mail.mailers.smtp.encryption')),
            'mail.from.address'            => setting('email.sender_email', config('mail.from.address')),
            'mail.from.name'               => setting('email.sender_name', config('mail.from.name')),
            'mail.default'                 => setting('email.mail_driver', 'smtp'),
        ]);
    }
}
