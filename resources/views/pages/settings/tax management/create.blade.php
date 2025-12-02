<div class="modal-body">
    <form action="{{ route('payroll.tax.store') }}" method="post">
        @csrf

        <div class="row">

            <!-- Salary From -->
            <div class="col-md-6 mb-3">
                <x-form.input-block>
                    <x-form.label> Salary Range From</x-form.label>
                    <x-form.input type="number" min="0" name="salary_from" required placeholder="e.g. 0" />
                </x-form.input-block>
            </div>

            <!-- Salary To -->
            <div class="col-md-6 mb-3">
                <x-form.input-block>
                    <x-form.label>Salary Range To</x-form.label>
                    <x-form.input type="number" min="0" name="salary_to" placeholder="e.g. 2000" />
                    <small class=" text-danger">Leave empty for last range (14,000+)</small>
                </x-form.input-block>
            </div>

            <!-- Percentage -->
             <div class="col-md-6 mb-3">
                <x-form.input 
                type="number" 
            step="0.01" 
            name="percentage" 
            required  
            placeholder="e.g. 15" 
            min="0" 
            max="100" 
                />

            </div>

            <!-- Deduction Amount -->
            <div class="col-md-6 mb-3">
                <x-form.input-block>
                    <x-form.label>Deduction Amount (Br)</x-form.label>
                    <x-form.input type="number" step="0.01" name="deducted_amount" required placeholder="e.g. 300" />
                </x-form.input-block>
            </div>

        </div>

        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">
                Save Tax Rate
            </x-form.button>
        </div>

    </form>
</div>
