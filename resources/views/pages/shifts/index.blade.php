@extends('layouts.app')

@section('title', $pageTitle)

@push('page-styles')
<style>
    .search-input-pill {
        border-radius: 50px !important;
        height: 46px !important;
        border: 1px solid #e3e3e3;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        padding-left: 20px !important;
    }
</style>
@endpush

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="#">HR</a></li>
                        <li class="breadcrumb-item active">{{ __('Shifts') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        @if(!$shiftsEnabled)
                            <span class="badge bg-danger-light text-danger d-flex align-items-center">
                                <i class="la la-exclamation-triangle me-1"></i> {{ __('Shifts Globally Disabled') }}
                            </span>
                        @endif
                        <span class="badge bg-{{ $shiftMode === 'mandatory' ? 'primary' : 'info' }}-light text-{{ $shiftMode === 'mandatory' ? 'primary' : 'info' }} d-flex align-items-center">
                            <i class="la la-cog me-1"></i> {{ __('Mode') }}: {{ ucfirst($shiftMode) }}
                        </span>
                        @if($nightShiftEnabled)
                            <a href="{{ route('admin.attendance-settings.night-shift') }}" class="btn btn-outline-info">
                                <i class="la la-moon"></i> {{ __('Night Shift Settings') }}
                            </a>
                        @endif
                        <a href="{{ route('admin.attendance-settings.index') }}#shifts_enabled" class="btn btn-outline-secondary">
                            <i class="la la-cog"></i> {{ __('Global Settings') }}
                        </a>
                        <a href="{{ route('shifts.assign') }}" class="btn btn-outline-primary">
                            <i class="la la-users"></i> {{ __('Assign Employees') }}
                        </a>
                        <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                            <i class="la la-plus"></i> {{ __('Create Shift') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Search Filter -->
                        <div class="row filter-row mb-4">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <input type="text" class="form-control search-input-pill" id="shiftSearch" placeholder="{{ __('Search by Name or Code') }}">
                                </div>
                            </div>
                        </div>
                        <!-- /Search Filter -->

                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="shiftsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Start Time') }}</th>
                                        <th>{{ __('End Time') }}</th>
                                        <th>{{ __('Grace In') }}</th>
                                        <th>{{ __('Grace Out') }}</th>
                                        <th>{{ __('Employees') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shifts as $shift)
                                        <tr class="shift-row" data-name="{{ strtolower($shift->name) }}" data-code="{{ strtolower($shift->code) }}">
                                            <td>
                                                <strong>{{ $shift->name }}</strong>
                                                @if($shift->description)
                                                    <br><small class="text-muted">{{ $shift->description }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge badge-info">{{ $shift->code }}</span></td>
                                            <td>{{ date('h:i A', strtotime($shift->start_time)) }}</td>
                                            <td>{{ date('h:i A', strtotime($shift->end_time)) }}</td>
                                            <td>
                                                <span class="badge bg-success-light text-success">{{ $shift->grace_period_minutes }} {{ __('min') }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-light text-info">{{ $shift->grace_out_minutes ?? 10 }} {{ __('min') }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('shifts.assign') }}?shift_id={{ $shift->id }}#shift-group-{{ $shift->id }}" title="{{ __('View Assigned Employees') }}">
                                                    <span class="badge badge-primary badge-pill">
                                                        {{ $shift->user_shifts_count ?? 0 }}
                                                    </span>
                                                </a>
                                            </td>
                                            <td>
                                                @if($shift->is_active)
                                                    <span class="badge badge-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="la la-ellipsis-h"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('shifts.edit', $shift->id) }}">
                                                            <i class="la la-pencil m-r-5"></i> {{ __('Edit') }}
                                                        </a>
                                                        <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" class="d-inline" 
                                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this shift?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="la la-trash m-r-5"></i> {{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                <div class="py-5">
                                                    <i class="la la-clock" style="font-size: 48px; color: #ccc;"></i>
                                                    <p class="text-muted mt-3">{{ __('No shifts created yet') }}</p>
                                                    <a href="{{ route('shifts.create') }}" class="btn btn-primary btn-sm mt-2">
                                                        <i class="la la-plus"></i> {{ __('Create Your First Shift') }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('shiftSearch');
        const rows = document.querySelectorAll('.shift-row');
        const tableBody = document.querySelector('#shiftsTable tbody');

        function filterShifts() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            rows.forEach(row => {
                const name = (row.dataset.name || '').toLowerCase().trim();
                const code = (row.dataset.code || '').toLowerCase().trim();

                // User specifically wants "starts with" for names
                // "Typing 'T' should show only shifts whose names start with T"
                const matchesName = name.startsWith(query);
                const matchesCode = code.includes(query);

                if (matchesName || matchesCode) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle "No matching shifts found" case
            let noMsg = document.getElementById('noShiftsFound');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noMsg) {
                    noMsg = document.createElement('tr');
                    noMsg.id = 'noShiftsFound';
                    noMsg.innerHTML = `<td colspan="9" class="text-center py-4">{{ __("No matching shifts found") }}</td>`;
                    if (tableBody) tableBody.appendChild(noMsg);
                }
            } else if (noMsg) {
                noMsg.remove();
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterShifts);
        }
    });
</script>
@endpush
