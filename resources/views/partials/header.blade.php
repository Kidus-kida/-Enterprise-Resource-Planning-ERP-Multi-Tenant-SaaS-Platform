<div class="header" style="height: 45px !important;">

    <!-- Logo -->
    <div style="text-align: center; transform: translateY(-8px);">
        <x-logo />
    </div>
    <!-- /Logo -->

    <a id="toggle_btn" href="javascript:void(0);" style="line-height: 45px !important;">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <!-- Header Title -->
    <div class="page-title-box" style="height: 45px !important; line-height: 45px !important; transform: translateY(-8px);">
        <h3>{{ Theme('name') ?? config('app.name') }}</h3>
    </div>
    <!-- /Header Title -->

    <a id="mobile_btn" class="mobile_btn" href="#sidebar" style="height: 45px !important; line-height: 45px !important;"><i class="fa-solid fa-bars"></i></a>

    <!-- Header Menu -->
    <ul class="nav user-menu" style="height: 45px !important;">

        <!-- Company Switcher -->
        @if(auth()->check() && (auth()->user()->business_id || request()->session()->has('user.business_id')) && !auth()->user()->isSystemOwner())
            @php
                // Simplified: Show company switcher for tenant owners OR users with business settings access
                // No need to check subscription limits here - if they can access tenant, they can manage companies
                $hasCompanyAccess = auth()->user()->isTenantOwner() || auth()->user()->can('business_settings.access');
                
                // In tenant DB, we trust isolation. Get all companies.
                $my_companies = \App\Company::where('is_active', 1)->get();
                $active_company_id = request()->session()->get('user.company_id');
                $active_company_ids = request()->session()->get('user.active_company_ids', []);
                
                // If not set, default to first or is_default
                if(!$active_company_id && $my_companies->count() > 0){
                    $default = $my_companies->where('is_default', 1)->first() ?? $my_companies->first();
                    $active_company_id = $default->id;
                    request()->session()->put('user.company_id', $active_company_id);
                    if (empty($active_company_ids)) {
                        $active_company_ids = [$active_company_id];
                        request()->session()->put('user.active_company_ids', $active_company_ids);
                    }
                }
                
                $active_company = $my_companies->where('id', $active_company_id)->first();
                
                // Display logic: show count if multiple selected, company name if single, or encourage creation
                if (count($active_company_ids) > 1) {
                    $display_name = count($active_company_ids) . ' Selected';
                } elseif ($active_company) {
                    $display_name = $active_company->name;
                } elseif ($my_companies->count() > 0) {
                    $display_name = 'Select Company';
                } else {
                    $display_name = 'Create Company';
                }
            @endphp
            
            @if($hasCompanyAccess)
                 <li class="nav-item dropdown has-arrow main-drop">
                    <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" style="line-height: 45px !important; height: 45px !important;">
                        <span><i class="fa fa-building"></i> {{ $display_name }}</span>
                    </a>
                    <div class="dropdown-menu" style="min-width: 250px; padding: 0;">
                        @if($my_companies->count() > 0)
                            <form action="{{ route('multi-companies.switch-multiple') }}" method="POST" id="company-switch-form">
                                @csrf
                                <div style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                    <span class="text-muted" style="font-size: 12px;">Select Companies</span>
                                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                                </div>
                                <div style="max-height: 300px; overflow-y: auto;">
                                    @foreach($my_companies as $comp)
                                        <div class="dropdown-item" onclick="event.stopPropagation();">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="company_ids[]" value="{{ $comp->id }}" id="comp_{{ $comp->id }}"
                                                    {{ in_array($comp->id, $active_company_ids) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="comp_{{ $comp->id }}" style="cursor: pointer; width: 100%;">
                                                    {{ $comp->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </form>
                        @else
                            {{-- No companies exist yet - show create option --}}
                            <div style="padding: 15px; text-align: center;">
                                <div style="margin-bottom: 10px;">
                                    <i class="fa fa-building" style="font-size: 24px; color: #ccc;"></i>
                                </div>
                                <p class="text-muted" style="margin-bottom: 10px; font-size: 14px;">No companies created yet</p>
                                <a href="{{ route('multi-companies.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Create First Company
                                </a>
                            </div>
                        @endif
                    </div>
                </li>
            @endif
        @elseif(auth()->check() && (auth()->user()->isTenantOwner() || auth()->user()->can('business_settings.access')) && !auth()->user()->isSystemOwner())
             {{-- Fallback for logic where business_id might not be set in session but user has access (edge case) --}}
             {{-- AND explicitly hide from System Owner here too --}}
             @php
                 // If we are here, likely business_id missing in session. 
                 // We can try to display simple switcher if companies exist.
                 // But since we rely on business_id mainly, this might be redundant.
                 // Let's keep it safe.
                 $display_name = 'Companies'; 
                 $my_companies = \App\Company::where('is_active', 1)->get();
             @endphp
             <li class="nav-item dropdown has-arrow main-drop">
                    <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" style="line-height: 45px !important; height: 45px !important;">
                        <span><i class="fa fa-building"></i> {{ $display_name }}</span>
                    </a>
                    <div class="dropdown-menu">
                         @if($my_companies->count() > 0)
                            @foreach($my_companies as $comp)
                                <a class="dropdown-item" href="{{ route('multi-companies.switch', [$comp->id]) }}">{{ $comp->name }}</a>
                            @endforeach
                        @else
                            <div style="padding: 15px; text-align: center;">
                                <p class="text-muted" style="margin-bottom: 10px;">No companies created yet</p>
                                <a href="{{ route('multi-companies.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Create Company
                                </a>
                            </div>
                        @endif
                    </div>
             </li>
        @endif


        <!-- Notifications -->
        <li class="nav-item dropdown has-arrow main-drop">
            <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" style="line-height: 45px !important; height: 45px !important;">
                <i class="fa-regular fa-bell"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="badge rounded-pill bg-primary">{{ auth()->user()->unreadNotifications->count() }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right notifications" style="min-width: 300px;">
                <div class="topnav-dropdown-header" style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                    <span class="notification-title">{{ __('Notifications') }}</span>
                    <a href="{{ route('notifications.clear') }}" class="clear-noti" style="font-size: 12px;">{{ __('Clear All') }}</a>
                </div>
                <div class="noti-content">
                    <ul class="notification-list" style="list-style: none; padding: 0; margin: 0; max-height: 300px; overflow-y: auto;">
                        @forelse(auth()->user()->notifications->take(5) as $notification)
                            <li class="notification-message" style="border-bottom: 1px solid #f5f5f5;">
                                <a href="{{ $notification->data['action_url'] ?? '#' }}" style="display: block; padding: 10px; color: #333;">
                                    <div class="media d-flex">
                                        <div class="media-body flex-grow-1">
                                            <p class="noti-details" style="margin-bottom: 5px; font-size: 13px;">{{ $notification->data['message'] ?? '' }}</p>
                                            <p class="noti-time" style="margin-bottom: 0;"><span class="notification-time" style="font-size: 11px; color: #999;">{{ $notification->created_at->diffForHumans() }}</span></p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="notification-message text-center p-3 text-muted">
                                {{ __('No notifications') }}
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="topnav-dropdown-footer" style="padding: 10px; border-top: 1px solid #eee; text-align: center;">
                    <a href="#" style="font-size: 12px;">{{ __('View all Notifications') }}</a>
                </div>
            </div>
        </li>

        <li class="nav-item dropdown has-arrow main-drop">
            <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" style="line-height: 45px !important; height: 45px !important;">
                <span class="user-img"><img src="{{ !empty(auth()->user()->avatar) ? uploadedAsset(auth()->user()->avatar,'users'): asset('images/user.jpg') }}" alt="User Image">
                    <span class="status online"></span></span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('profile') }}">{{ __('My Profile') }}</a>
                <a onclick="document.getElementById('logout_user_form').submit()" class="dropdown-item logout_btn" href="javascript:void(0);">Logout</a>
            </div>
        </li>
    </ul>
    <!-- /Header Menu -->

    <!-- Mobile Menu -->
    <div class="dropdown mobile-user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i
                class="fa-solid fa-ellipsis-vertical"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="profile.html">My Profile</a>
            <a onclick="document.getElementById('logout_user_form').submit()" class="dropdown-item logout_btn" href="javascript:void(0);">Logout</a>
        </div>
    </div>
    <!-- /Mobile Menu -->
    <form action="{{ route('logout') }}" id="logout_user_form" method="post">@csrf</form>

</div>
