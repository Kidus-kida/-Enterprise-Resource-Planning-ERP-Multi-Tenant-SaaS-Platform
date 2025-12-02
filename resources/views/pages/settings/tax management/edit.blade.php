<div class="modal-body">
    <form action="{{ route('payroll.tax.update', $tax->id) }}" method="post">
        @csrf
        @method("PUT")

        <div class="row">
            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Salary From') }}</x-form.label>
                    <x-form.input type="number" name="salary_from" value="{{ $tax->salary_from }}" required />
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Salary To') }}</x-form.label>
                    <x-form.input type="number" name="salary_to" value="{{ $tax->salary_to }}" required />
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Percentage (%)') }}</x-form.label>
                    <x-form.input type="number" step="0.01" name="percentage" value="{{ $tax->percentage }}" required />
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Deducted Amount') }}</x-form.label>
                    <x-form.input type="number" name="deducted_amount" value="{{ $tax->deducted_amount }}" required />
                </x-form.input-block>
            </div>
        </div>

        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Update') }}</x-form.button>
        </div>

    </form>
</div>
