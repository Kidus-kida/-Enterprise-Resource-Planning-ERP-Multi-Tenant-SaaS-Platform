<div class="col-md-6 col-lg-4 col-xl-3 mb-3">
    <div class="card h-97 odoo-card position-relative border shadow-sm">
        <div class="card-body p-3 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold text-dark mb-0 text-truncate" title="{{ $department->name }}">
                    {{ $department->name }}
                </h5>
                <div class="dropdown">
                    <button class="btn btn-link p-0 text-muted" type="button" data-bs-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" 
                               data-url="{{ route('departments.edit', $department->id) }}"
                               data-ajax-modal="true" data-title="Edit Department">
                                {{ __('Edit') }}
                            </a>
                        </li>
                        <li>
                             <a class="dropdown-item deleteBtn" href="javascript:void(0)"
                                data-route="{{ route('departments.destroy', $department->id) }}"
                                data-title="{{ __('Delete Department') }}"
                                data-question="{{ __('Are you sure?') }}">
                                {{ __('Delete') }}
                             </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mb-3 text-muted small">
                @if($department->location || $department->company_name)
                    <div class="mb-1"><i class="fa fa-map-marker me-1"></i> {{ $department->company_name ?? $department->location }}</div>
                @endif
                
                @if($department->manager)
                    <div class="d-flex align-items-center">
                        <i class="fa fa-user me-2"></i> 
                        <span>{{ $department->manager->fullname ?? 'N/A' }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-auto d-flex justify-content-between align-items-center">
                <a href="{{ route('employees.index', ['department_id' => $department->id]) }}" 
                   class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <span class="fw-bold">{{ $department->employee_details_count }}</span> {{ __('Employees') }}
                </a>
            </div>
        </div>
        <div class="card-footer p-0 border-0" style="height: 5px; background-color: {{ $department->color ?? '#007bff' }}; border-radius: 0 0 4px 4px;"></div>
    </div>
</div>
