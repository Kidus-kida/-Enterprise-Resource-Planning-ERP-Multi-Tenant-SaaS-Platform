<div class="col-md-6 col-lg-4 col-xl-3 mb-3">
    <div class="card odoo-employee-card border shadow-sm position-relative overflow-hidden" style="min-height: 120px;">
        <div class="d-flex h-100">
            <!-- Avatar Section - Full Height Left -->
            <div class="flex-shrink-0" style="width: 90px;">
                <a href="{{ route('employees.show', ['employee' => \Crypt::encrypt($employee->id)]) }}" class="d-block h-100">
                    @if(!empty($employee->avatar))
                        <img src="{{ uploadedAsset($employee->avatar,'users') }}" 
                             alt="{{ $employee->fullname }}" 
                             class="h-100 w-100" 
                             style="object-fit: cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center text-white fw-bold h-100 w-100" 
                             style="background: {{ '#' . substr(md5($employee->id), 0, 6) }}; font-size: 32px;">
                            {{ strtoupper(substr($employee->firstname ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </a>
            </div>

            <!-- Info Section -->
            <div class="flex-grow-1 min-width-0 p-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h4 class="mb-0 fw-bold text-truncate">
                        <a href="{{ route('employees.show', ['employee' => \Crypt::encrypt($employee->id)]) }}" 
                           class="text-dark text-decoration-none">
                            {{ $employee->fullname }}
                        </a>
                    </h4>
                    <div class="dropdown">
                        <button class="btn btn-link p-0 text-muted" type="button" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" 
                                   data-url="{{ route('employees.edit', ['employee' => \Crypt::encrypt($employee->id)]) }}" 
                                   data-ajax-modal="true" data-title="Edit Employee" data-size="lg">
                                    <i class="fa-solid fa-pencil me-2"></i> {{ __('Edit') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item deleteBtn" href="javascript:void(0)"
                                   data-route="{{ route('employees.destroy', $employee->id) }}" 
                                   data-title="Delete Employee" data-question="Are you sure you want to delete?">
                                    <i class="fa-regular fa-trash-can me-2"></i> {{ __('Delete') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                @if (!empty($employee->employeeDetail) && !empty($employee->employeeDetail->designation))
                    <div class="text-muted small mb-2">
                        <i class="fa fa-briefcase me-1"></i>{{ $employee->employeeDetail->designation->name }}
                    </div>
                @endif

                @if($employee->email)
                    <div class="text-muted small text-truncate mb-1" title="{{ $employee->email }}">
                        <i class="fa fa-envelope me-1"></i>{{ $employee->email }}
                    </div>
                @endif

                @if($employee->phone)
                    <div class="text-muted small">
                        <i class="fa fa-phone me-1"></i>{{ $employee->phone }}
                    </div>
                @endif

                @if (!empty($employee->employeeDetail) && !empty($employee->employeeDetail->date_joined))
                    <div class="text-muted small mt-2">
                        <i class="fa fa-calendar me-1"></i>{{ $employee->employeeDetail->date_joined->format('M d, Y') }}
                    </div>
                @endif

                @if($employee->is_active == 0)
                    <span class="badge bg-danger mt-2">Archived</span>
                @endif
            </div>
        </div>
    </div>
</div>
