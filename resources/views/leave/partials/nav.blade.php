@push('page-styles')
<link rel="stylesheet" href="{{ asset('css/leave-management.css') }}">
@endpush

{{-- Odoo-style Dropdown Navigation --}}
<div class="leave-nav-container mb-3">
    <div class="d-flex align-items-center justify-content-between">
        {{-- Left: Dropdown Navigation Button --}}
        <div class="dropdown flex-shrink-0">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" id="leaveNavDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @php
                    $currentSection = 'Leave Management';
                    $currentIcon = 'fa-calendar-alt';
                    
                    if (request()->routeIs('leave.my-time*')) {
                        $currentSection = __('My Time');
                        $currentIcon = 'fa-user-clock';
                    } elseif (request()->routeIs('leave.overview*')) {
                        $currentSection = __('Overview');
                        $currentIcon = 'fa-users';
                    } elseif (request()->routeIs('leave.management*')) {
                        $currentSection = __('Management');
                        $currentIcon = 'fa-tasks';
                    } elseif (request()->routeIs('leave.reporting*')) {
                        $currentSection = __('Reporting');
                        $currentIcon = 'fa-chart-bar';
                    } elseif (request()->routeIs(['leave.configuration*', 'leave.config.*'])) {
                        $currentSection = __('Configuration');
                        $currentIcon = 'fa-cog';
                    }
                @endphp
                <i class="fa {{ $currentIcon }}"></i>
                <span class="fw-semibold">{{ $currentSection }}</span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="leaveNavDropdown">
                <li>
                    <a class="dropdown-item {{ request()->routeIs('leave.my-time*') ? 'active' : '' }}" 
                       href="{{ route('leave.my-time') }}">
                        <i class="fa fa-user-clock me-2"></i>
                        {{ __('My Time') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('leave.overview*') ? 'active' : '' }}" 
                       href="{{ route('leave.overview') }}">
                        <i class="fa fa-users me-2"></i>
                        {{ __('Overview') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('leave.management*') ? 'active' : '' }}" 
                       href="{{ route('leave.management') }}">
                        <i class="fa fa-tasks me-2"></i>
                        {{ __('Management') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('leave.reporting*') ? 'active' : '' }}" 
                       href="{{ route('leave.reporting') }}">
                        <i class="fa fa-chart-bar me-2"></i>
                        {{ __('Reporting') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs(['leave.configuration*', 'leave.config.*']) ? 'active' : '' }}" 
                       href="{{ route('leave.configuration') }}">
                        <i class="fa fa-cog me-2"></i>
                        {{ __('Configuration') }}
                    </a>
                </li>
            </ul>
        </div>

        {{-- Center: Search Bar (Optional) --}}
        <div class="d-flex justify-content-center flex-grow-1 mx-3">
            @if(isset($searchConfig))
                <div style="width: 100%; max-width: 600px;">
                     <x-odoo-search-bar 
                         :action="$searchConfig['action'] ?? request()->url()" 
                         :fields="$searchConfig['fields'] ?? [['key' => 'name', 'label' => 'Name']]"
                         :filterOptions="$searchConfig['filters'] ?? []"
                         :groupByOptions="$searchConfig['groups'] ?? []"
                         :targetSelector="$searchConfig['target'] ?? '.leave-card'"
                     />
                </div>
            @endif
        </div>

        {{-- Right: Configuration Quick Access Icon --}}
        <div class="config-quick-access dropdown flex-shrink-0">
            <button class="btn btn-light btn-icon" id="configDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Quick Configuration') }}">
                <i class="fa fa-cog"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="configDropdown">
                <li><h6 class="dropdown-header">{{ __('Configuration') }}</h6></li>
                <li><a class="dropdown-item" href="{{ route('leave.config.time-off-types.index') }}">
                    <i class="fa fa-list me-2"></i> {{ __('Time Off Types') }}
                </a></li>
                <li><a class="dropdown-item" href="{{ route('leave.config.accrual-plans.index') }}">
                    <i class="fa fa-calendar-plus me-2"></i> {{ __('Accrual Plans') }}
                </a></li>
                <li><a class="dropdown-item" href="{{ route('leave.config.public-holidays.index') }}">
                    <i class="fa fa-calendar-day me-2"></i> {{ __('Public Holidays') }}
                </a></li>
                <li><a class="dropdown-item" href="{{ route('leave.config.mandatory-days.index') }}">
                    <i class="fa fa-exclamation-circle me-2"></i> {{ __('Mandatory Days') }}
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('leave.configuration') }}">
                    <i class="fa fa-sliders-h me-2"></i> {{ __('General Settings') }}
                </a></li>
            </ul>
        </div>
    </div>
</div>

@push('page-scripts')
<script src="{{ asset('js/leave-management.js') }}"></script>
@endpush
