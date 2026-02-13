<div class="milestone-item border rounded p-3 mb-3 bg-light shadow-sm position-relative">
    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-milestone"
        onclick="removeMilestone(this)"></button>

    <div class="row">
        @if(isset($level) && $level->id)
            <input type="hidden" name="levels[{{ $index }}][id]" value="{{ $level->id }}">
        @endif

        <div class="col-md-12 mb-3">
            <span class="badge bg-primary milestone-count text-uppercase">
                {{ __('Milestone') }} #<span class="seq">{{ $index === 'INDEX' ? 1 : $index + 1 }}</span>
            </span>
            <input type="hidden" name="levels[{{ $index }}][sequence]" class="milestone-seq"
                value="{{ isset($level) ? $level->sequence : 1 }}">
        </div>

        {{-- Tenure Requirement --}}
        <div class="col-md-5 mb-3">
            <label class="form-label fw-bold">{{ __('This milestone will be reached') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <input type="radio" class="form-check-input mt-0" name="levels[{{ $index }}][reached_mode]" value="start" 
                        {{ isset($level) && $level->start_count == 0 ? 'checked' : ((!isset($level)) ? 'checked' : '') }}
                        onchange="toggleTenureInput(this)">
                </span>
                <span class="form-control border-start-0">{{ __('At allocation creation') }}</span>
            </div>
            <div class="input-group mt-2">
                <span class="input-group-text bg-white border-end-0">
                    <input type="radio" class="form-check-input mt-0" name="levels[{{ $index }}][reached_mode]" value="after"
                        {{ isset($level) && $level->start_count > 0 ? 'checked' : '' }}
                        onchange="toggleTenureInput(this)">
                </span>
                <span class="input-group-text border-start-0 border-end-0 bg-white text-muted">{{ __('After') }}</span>
                <input type="number" name="levels[{{ $index }}][start_count]" class="form-control border-start-0 tenure-input {{ isset($level) && $level->start_count == 0 || !isset($level) ? 'd-none' : '' }}" 
                    value="{{ isset($level) ? $level->start_count : 0 }}" min="0">
                <select name="levels[{{ $index }}][start_type]" class="form-select border-start-0 tenure-type {{ isset($level) && $level->start_count == 0 || !isset($level) ? 'd-none' : '' }}">
                    <option value="days" {{ isset($level) && $level->start_type == 'days' ? 'selected' : '' }}>{{ __('Days') }}</option>
                    <option value="months" {{ isset($level) && $level->start_type == 'months' ? 'selected' : '' }}>{{ __('Months') }}</option>
                    <option value="years" {{ isset($level) && $level->start_type == 'years' ? 'selected' : '' }}>{{ __('Years') }}</option>
                </select>
            </div>
        </div>

        {{-- Accrual Rate & Frequency (Odoo Sentence Style) --}}
        <div class="col-md-7 mb-3">
            <label class="form-label fw-bold">{{ __('Set the employee accrual frequency') }}</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <input type="number" step="0.0001" name="levels[{{ $index }}][accrual_amount]" class="form-control" style="width: 100px;"
                    placeholder="0.0000" value="{{ isset($level) ? $level->accrual_amount : '' }}" required>
                
                <select name="levels[{{ $index }}][accrual_unit]" class="form-select accrual-unit-select" style="width: 110px;" onchange="updateUnitLabels(this)">
                    <option value="days" {{ isset($level) && $level->accrual_unit == 'days' ? 'selected' : '' }}>{{ __('Day(s)') }}</option>
                    <option value="hours" {{ isset($level) && $level->accrual_unit == 'hours' ? 'selected' : '' }}>{{ __('Hour(s)') }}</option>
                </select>

                <span class="text-muted">{{ __('per') }}</span>

                <select name="levels[{{ $index }}][accrual_frequency]" class="form-select" style="width: 130px;">
                    @foreach(['hourly', 'daily', 'weekly', 'biweekly', 'monthly', 'biyearly', 'yearly'] as $freq)
                        <option value="{{ $freq }}" {{ (isset($level) && $level->accrual_frequency == $freq) || (!isset($level) && $freq == 'monthly') ? 'selected' : '' }}>
                            {{ ucfirst($freq) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-text small mt-1">{{ __('Example: 1 Hour(s) per Monthly or 2 Day(s) per Yearly') }}</div>
        </div>

        <div class="col-md-12 mb-2">
            <hr>
            <div class="row">
                <div class="col-md-6 border-end">
                    <h6 class="text-muted text-uppercase mb-3 small fw-bold">{{ __('Carry Over Options') }}</h6>
                    <div class="mb-3">
                        <label class="form-label d-block">{{ __('After a year, unused time off will be:') }}</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="levels[{{ $index }}][action_with_unused_accruals]" value="lost" id="lost_{{ $index }}"
                                onchange="toggleCarryOverToggles(this)" {{ isset($level) && $level->action_with_unused_accruals == 'lost' ? 'checked' : '' }}>
                            <label class="form-check-label" for="lost_{{ $index }}">{{ __('Lost') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="levels[{{ $index }}][action_with_unused_accruals]" value="all" id="carried_{{ $index }}"
                                onchange="toggleCarryOverToggles(this)" {{ !isset($level) || $level->action_with_unused_accruals != 'lost' ? 'checked' : '' }}>
                            <label class="form-check-label" for="carried_{{ $index }}">{{ __('Carried over') }}</label>
                        </div>
                    </div>
                    
                    <div class="carryover-settings-container" style="{{ isset($level) && $level->action_with_unused_accruals == 'lost' ? 'display: none;' : '' }}">
                        {{-- How much time can be carried over --}}
                        <div class="mb-3">
                            <label class="form-label d-block">{{ __('How much time can be carried over:') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="levels[{{ $index }}][carryover_mode]" value="unlimited" id="unlimited_{{ $index }}"
                                    onchange="toggleMaxCarryInput(this)" {{ !isset($level) || is_null($level->max_carryover) ? 'checked' : '' }}>
                                <label class="form-check-label" for="unlimited_{{ $index }}">{{ __('Unlimited') }}</label>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mt-1">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="radio" name="levels[{{ $index }}][carryover_mode]" value="up_to" id="up_to_{{ $index }}"
                                        onchange="toggleMaxCarryInput(this)" {{ isset($level) && !is_null($level->max_carryover) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="up_to_{{ $index }}">{{ __('Up to') }}</label>
                                </div>
                                <div class="max-carry-input-group align-items-center gap-1" style="{{ isset($level) && !is_null($level->max_carryover) ? 'display: flex;' : 'display: none;' }}">
                                    <input type="number" step="0.0001" name="levels[{{ $index }}][max_carryover]" class="form-control form-control-sm" style="width: 80px;"
                                        value="{{ isset($level) ? $level->max_carryover : '' }}">
                                    <select name="levels[{{ $index }}][max_carryover_unit]" class="form-select form-select-sm" style="width: 100px;">
                                        <option value="days" {{ isset($level) && $level->max_carryover_unit == 'days' ? 'selected' : '' }}>{{ __('Day(s)') }}</option>
                                        <option value="hours" {{ isset($level) && $level->max_carryover_unit == 'hours' ? 'selected' : '' }}>{{ __('Hour(s)') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Validity --}}
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_validity_{{ $index }}" 
                                    onchange="toggleValidityInput(this)" {{ isset($level) && !is_null($level->carryover_validity_period) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="has_validity_{{ $index }}">{{ __('Define a carry over validity?') }}</label>
                            </div>
                            <div class="validity-input-container mt-1 align-items-center gap-2" style="{{ isset($level) && !is_null($level->carryover_validity_period) ? 'display: flex;' : 'display: none;' }}">
                                <span class="small text-muted">{{ __('The days carried over will be effective for') }}</span>
                                <input type="number" name="levels[{{ $index }}][carryover_validity_period]" class="form-control form-control-sm" style="width: 70px;"
                                    value="{{ isset($level) ? $level->carryover_validity_period : '' }}">
                                <span class="small text-muted">{{ __('Days') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase mb-3 small fw-bold">{{ __('Cap Options') }}</h6>
                    
                    {{-- Yearly Cap --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input cap-checkbox" type="checkbox" id="has_yearly_cap_{{ $index }}" 
                                    {{ isset($level) && $level->yearly_cap > 0 ? 'checked' : '' }} onchange="toggleCapInput(this)">
                                <label class="form-check-label fw-bold" for="has_yearly_cap_{{ $index }}">
                                    {{ __('Define a yearly cap?') }}
                                </label>
                            </div>
                            <div class="cap-input-container align-items-center gap-2" style="{{ isset($level) && $level->yearly_cap > 0 ? 'display: flex;' : 'display: none;' }}">
                                <span class="small text-muted">{{ __("Accrual will stop until next carry-over date if accrued time's reach") }}</span>
                                <input type="number" step="0.0001" name="levels[{{ $index }}][yearly_cap]" class="form-control form-control-sm" style="width: 80px;"
                                    placeholder="0.0000" value="{{ isset($level) ? $level->yearly_cap : '' }}">
                                <select name="levels[{{ $index }}][yearly_cap_unit]" class="form-select form-select-sm" style="width: 100px;">
                                    <option value="days" {{ isset($level) && $level->yearly_cap_unit == 'days' ? 'selected' : '' }}>{{ __('Day(s)') }}</option>
                                    <option value="hours" {{ isset($level) && $level->yearly_cap_unit == 'hours' ? 'selected' : '' }}>{{ __('Hour(s)') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Balance Cap --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input cap-checkbox" type="checkbox" id="has_balance_cap_{{ $index }}" 
                                    {{ isset($level) && $level->cap_accrued_time > 0 ? 'checked' : '' }} onchange="toggleCapInput(this)">
                                <label class="form-check-label fw-bold" for="has_balance_cap_{{ $index }}">
                                    {{ __('Define a balance cap?') }}
                                </label>
                            </div>
                            <div class="cap-input-container align-items-center gap-2" style="{{ isset($level) && $level->cap_accrued_time > 0 ? 'display: flex;' : 'display: none;' }}">
                                <span class="small text-muted">{{ __("The plan will be on hold if the balance reach") }}</span>
                                <input type="number" step="0.0001" name="levels[{{ $index }}][cap_accrued_time]" class="form-control form-control-sm" style="width: 80px;"
                                    placeholder="0.0000" value="{{ isset($level) ? $level->cap_accrued_time : '' }}">
                                <select name="levels[{{ $index }}][balance_cap_unit]" class="form-select form-select-sm" style="width: 100px;">
                                    <option value="days" {{ isset($level) && $level->balance_cap_unit == 'days' ? 'selected' : '' }}>{{ __('Day(s)') }}</option>
                                    <option value="hours" {{ isset($level) && $level->balance_cap_unit == 'hours' ? 'selected' : '' }}>{{ __('Hour(s)') }}</option>
                                </select>
                                <span class="small text-muted">{{ __("of available time.") }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>