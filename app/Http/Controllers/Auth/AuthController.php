<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{

    public function login()
    {
        // Check query param first, then sticky session
        $tenantId = request()->query('tenant') ?? session('sticky_tenant_id');
        
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $this->data['pageTitle'] = __('Login');
        $this->data['tenant_id'] = $tenantId;
        return view('auth.login', $this->data);
    }
    
    public function tenantLogin($tenantId)
    {
        session(['sticky_tenant_id' => $tenantId]);
        return redirect()->route('login');
    }

    public function loginAuth(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        // Check for tenant from BOTH query parameter AND subdomain (set by middleware)
        $tenantId = $request->query('tenant') ?? $request->input('tenant') ?? session('current_tenant_id');
        
        \Log::info("AuthController: loginAuth - Tenant ID: " . ($tenantId ?? 'NONE') . ", Email: " . $request->email);
        
        // Logic for TENANT Login overriding default Auth
        if ($tenantId) {
            \Log::info("AuthController: Tenant login mode for tenant: $tenantId");
            
            // Self-contained connection setup - DO NOT RELY ON MIDDLEWARE HERE
            $this->setupTenantConnection($tenantId);

            // Ensure connection is set
            if (config('database.connections.tenant')) {
                 // Force a new user instance on the tenant connection
                 $tenantUser = (new \App\Models\User)->setConnection('tenant')->where('email', $request->email)->first();
                 
                 if ($tenantUser) {
                     if (Hash::check($request->password, $tenantUser->password)) {
                         if ($tenantUser->is_active === 1) {
                             // SUCCESS: Manual Login
                             Auth::guard('web')->login($tenantUser);
                             session(['current_tenant_id' => $tenantId]);
                             $tenantUser->update(['is_online' => true]);
                             
                             \Log::info("AuthController: Tenant login SUCCESS for user {$tenantUser->id}");
                             return redirect()->route('dashboard');
                         }
                         \Log::warning("AuthController: Tenant user account disabled: " . $request->email);
                         return back()->withErrors(['email' => 'Your account is disabled.']);
                     }
                     \Log::warning("AuthController: Tenant login - incorrect password for: " . $request->email);
                     return back()->withErrors(['password' => 'Incorrect Password']);
                 }
                 // If not found in tenant, fall through? No, tenant login should be strict.
                 \Log::warning("AuthController: User not found in tenant DB: " . $request->email);
                 return back()->withErrors(['email' => 'Account could not be found in Tenant Database.']);
            }
            
            \Log::error("AuthController: Tenant connection could not be established for tenant: $tenantId");
            return back()->withErrors(['email' => 'Tenant database connection failed.']);
        }

        // ORIGINAL LOGIC (Master DB) - SuperAdmin/Central Login
        \Log::info("AuthController: Central/SuperAdmin login mode");
        $user = User::where('email', $request->email)->first();
        
        if (!empty($user)) {
            if ($user->is_active === 1) {
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials)) {
                    // Start session...
                    $user->update([
                        'is_online' => true,
                    ]);
                    \Log::info("AuthController: Central login SUCCESS for user {$user->id}");
                    return redirect()->route('dashboard');
                }
                return back()->withErrors(['password' => 'Incorrect Password']);
            }
            return back()->withErrors(['email' => 'Your account is disabled.']);
        }
        return back()->withErrors(['email' => 'Account could not be found.']);
    }

    public function forgotPassword()
    {
        $this->data['pageTitle'] = __('Forgot Password');
        return view('auth.forgot-password', $this->data);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(notify(__($status)))
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(string $token)
    {
        $this->data['pageTitle'] = __('Reset Password');
        $this->data['token'] = $token;
        return view('auth.password-reset', $this->data);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with(notify(__($status)))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request)
    {
        auth()->user()->update([
            'is_online' => true,
        ]);
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }

    private function setupTenantConnection($tenantId)
    {
        try {
            $tenant = \Modules\Superadmin\Models\Tenant::on('mysql')->find($tenantId);

            if ($tenant && !empty($tenant->data) && isset($tenant->data['db_host'])) {
                $credentials = $tenant->data;

                if (isset($credentials['db_name'], $credentials['db_username'], $credentials['db_password'])) {
                    try {
                        $password = decrypt($credentials['db_password']);
                    } catch (\Exception $e) {
                        $password = $credentials['db_password'];
                    }

                    \Illuminate\Support\Facades\Config::set('database.connections.tenant', [
                        'driver' => 'mysql',
                        'host' => $credentials['db_host'],
                        'port' => $credentials['db_port'] ?? 3306,
                        'database' => $credentials['db_name'],
                        'username' => $credentials['db_username'],
                        'password' => $password,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => false,
                    ]);

                    \Illuminate\Support\Facades\DB::purge('tenant');
                    \Illuminate\Support\Facades\DB::reconnect('tenant');
                }
            }
        } catch (\Exception $e) {
            \Log::error("AuthController: Failed to setup tenant connection: " . $e->getMessage());
        }
    }
}
