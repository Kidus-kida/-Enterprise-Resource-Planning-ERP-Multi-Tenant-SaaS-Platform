@extends('layouts.app')

@section('page-header')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Edit Accrual Plan') }}: {{ $accrualPlan->name }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('leave.config.accrual-plans.index') }}">{{ __('Accrual Plans') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Edit') }}</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('page-content')
    <div class="content container-fluid">
        @include('leave.partials.nav')

        <form action="{{ route('leave.config.accrual-plans.update', $accrualPlan->id) }}" method="POST"
            id="accrualPlanForm">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('Plan Configuration') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('Plan Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $accrualPlan->name) }}" required
                                placeholder="e.g. Standard Annual PTO">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">{{ __('Accrued Gain Time') }}</label>
                            <select name="accrued_gain_time" id="accrued_gain_time" class="form-select"
                                onchange="toggleWorkedTime()">
                                <option value="start" {{ $accrualPlan->accrued_gain_time == 'start' ? 'selected' : '' }}>
                                    {{ __('At the start of the accrual period') }}
                                </option>
                                <option value="end" {{ $accrualPlan->accrued_gain_time == 'end' ? 'selected' : '' }}>
                                    {{ __('At the end of the accrual period') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">{{ __('Transition Mode') }}</label>
                            <select name="transition_mode" class="form-select">
                                <option value="immediately" {{ $accrualPlan->transition_mode == 'immediately' ? 'selected' : '' }}>{{ __('Immediately') }}</option>
                                <option value="after_accrual" {{ $accrualPlan->transition_mode == 'after_accrual' ? 'selected' : '' }}>{{ __('After next accrual') }}</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3" id="worked_time_container"
                            style="{{ $accrualPlan->accrued_gain_time == 'end' ? '' : 'display: none;' }}">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_based_on_worked_time"
                                    id="is_based_on_worked_time" value="1" {{ $accrualPlan->is_based_on_worked_time ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold"
                                    for="is_based_on_worked_time">{{ __('Based on worked time') }}</label>
                                <div class="form-text text-muted">
                                    {{ __('If checked, the accrual will be prorated based on the employee\'s attendance.') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6 class="fw-bold mb-3">{{ __('Carry-over Rules') }}</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('Carry-over Timing') }}</label>
                            <select name="carry_over_time" class="form-select" id="carry_over_time"
                                onchange="toggleCarryOverDate()">
                                <option value="year_start" {{ $accrualPlan->carry_over_time == 'year_start' ? 'selected' : '' }}>{{ __('At the beginning of the year') }}</option>
                                <option value="allocation" {{ $accrualPlan->carry_over_time == 'allocation' ? 'selected' : '' }}>{{ __('At the allocation date') }}</option>
                                <option value="other" {{ $accrualPlan->carry_over_time == 'other' ? 'selected' : '' }}>
                                    {{ __('At a custom date') }}
                                </option>
                            </select>
                        </div>

                        <div id="custom_carryover_date" class="col-md-8 row"
                            style="{{ $accrualPlan->carry_over_time == 'other' ? '' : 'display: none;' }}">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Carry-over Month') }}</label>
                                <select name="carry_over_month" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $accrualPlan->carry_over_month == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Carry-over Day') }}</label>
                                <input type="number" name="carry_over_day" class="form-control" min="1" max="31"
                                    value="{{ $accrualPlan->carry_over_day ?? 1 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Milestones Section --}}
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Milestones (Accrual Tiers)') }}</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMilestone()">
                        <i class="fa fa-plus"></i> {{ __('Add Milestone') }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="milestones_container">
                        {{-- Pre-existing milestones --}}
                        @foreach($accrualPlan->levels as $index => $level)
                            @include('leave.configuration.accrual-plans.partials.milestone_item', ['index' => $index, 'level' => $level])
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('leave.config.accrual-plans.index') }}"
                    class="btn btn-light btn-lg">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary btn-lg px-5">{{ __('Update Accrual Plan') }}</button>
            </div>
        </form>
    </div>

    {{-- Milestone Template --}}
    <template id="milestone-template">
        @include('leave.configuration.accrual-plans.partials.milestone_item', ['index' => 'INDEX', 'level' => null])
    </template>

    @push('page-scripts')
        <script>
            {
                let milestoneIndex = {{ $accrualPlan->levels->count() }};

                function addMilestone() {
                    const container = document.getElementById('milestones_container');
                    const template = document.getElementById('milestone-template').innerHTML;
                    const html = template.replace(/INDEX/g, milestoneIndex);

                    const div = document.createElement('div');
                    div.innerHTML = html;
                    container.appendChild(div.firstElementChild);

                    updateMilestoneSequences();
                    milestoneIndex++;
                }

                window.addMilestone = addMilestone;

                document.addEventListener('DOMContentLoaded', function () {
                    // Only add if empty (helpful for Create page, less so for Edit but good for consistency)
                    const container = document.getElementById('milestones_container');
                    if (container && container.children.length === 0) {
                        addMilestone();
                    }
                    toggleWorkedTime();
                    toggleCarryOverDate();
                });

                function removeMilestone(btn) {
                    if (confirm('{{ __("Are you sure you want to remove this milestone?") }}')) {
                        btn.closest('.milestone-item').remove();
                        updateMilestoneSequences();
                    }
                }

                window.removeMilestone = removeMilestone;

                function updateMilestoneSequences() {
                    const items = document.querySelectorAll('.milestone-item');
                    items.forEach((item, index) => {
                        const seq = index + 1;
                        item.querySelector('.seq').innerText = seq;
                        const seqField = item.querySelector('.milestone-seq');
                        if (seqField) seqField.value = seq;
                    });
                }

                window.updateMilestoneSequences = updateMilestoneSequences;

                function toggleCarryOverDate() {
                    const type = document.getElementById('carry_over_time').value;
                    const container = document.getElementById('custom_carryover_date');
                    container.style.display = (type === 'other') ? 'flex' : 'none';
                }

                window.toggleCarryOverDate = toggleCarryOverDate;

                function toggleMaxCarry(select) {
                    const item = select.closest('.milestone-item');
                    const field = item.querySelector('.max-carry-field');
                    field.style.display = (select.value === 'maximum') ? 'block' : 'none';
                }

                window.toggleMaxCarry = toggleMaxCarry;

                function toggleWorkedTime() {
                    const gainTime = document.getElementById('accrued_gain_time').value;
                    const container = document.getElementById('worked_time_container');
                    container.style.display = (gainTime === 'end') ? 'block' : 'none';

                    // If hiding, uncheck the box to be safe
                    if (gainTime !== 'end') {
                        document.getElementById('is_based_on_worked_time').checked = false;
                    }
                }

                window.toggleWorkedTime = toggleWorkedTime;

                function toggleTenureInput(radio) {
                    const item = radio.closest('.milestone-item');
                    const startsAfter = radio.value === 'after';
                    const input = item.querySelector('.tenure-input');
                    const select = item.querySelector('.tenure-type');

                    if (!startsAfter) {
                        input.value = 0;
                        if (input) input.classList.add('d-none');
                        if (select) select.classList.add('d-none');
                    } else {
                        if (input) input.classList.remove('d-none');
                        if (select) select.classList.remove('d-none');
                    }
                }

                window.toggleTenureInput = toggleTenureInput;

                function toggleCapInput(checkbox) {
                    const container = checkbox.closest('.mb-3').querySelector('.cap-input-container');
                    container.style.display = checkbox.checked ? 'flex' : 'none';
                    if (!checkbox.checked) {
                        container.querySelector('input').value = '';
                    }
                }

                window.toggleCapInput = toggleCapInput;

                function toggleCarryOverToggles(radio) {
                    const item = radio.closest('.milestone-item');
                    const container = item.querySelector('.carryover-settings-container');
                    container.style.display = (radio.value === 'all') ? 'block' : 'none';
                }

                window.toggleCarryOverToggles = toggleCarryOverToggles;

                function toggleMaxCarryInput(radio) {
                    const container = radio.closest('.mb-3').querySelector('.max-carry-input-group');
                    container.style.display = (radio.value === 'up_to') ? 'flex' : 'none';
                    if (radio.value === 'unlimited') {
                        container.querySelector('input').value = '';
                    }
                }

                window.toggleMaxCarryInput = toggleMaxCarryInput;

                function toggleValidityInput(checkbox) {
                    const container = checkbox.closest('.mb-3').querySelector('.validity-input-container');
                    container.style.display = checkbox.checked ? 'flex' : 'none';
                    if (!checkbox.checked) {
                        container.querySelector('input').value = '';
                    }
                }

                window.toggleValidityInput = toggleValidityInput;

                function updateUnitLabels(select) {
                    const item = select.closest('.milestone-item');
                    const labels = item.querySelectorAll('.unit-label');
                    const unit = select.options[select.selectedIndex].text;
                    labels.forEach(l => l.innerText = unit);
                }

                window.updateUnitLabels = updateUnitLabels;

                document.addEventListener('DOMContentLoaded', function () {
                    toggleWorkedTime();
                    toggleCarryOverDate();
                });
            }
        </script>
        <style>
            .milestone-item {
                border-left: 4px solid #0d6efd !important;
            }

            .badge {
                font-weight: 600;
                padding: 0.5rem 0.8rem;
            }

            .milestone-item.bg-light {
                background-color: #f8f9fa !important;
            }
        </style>
    @endpush
@endsection