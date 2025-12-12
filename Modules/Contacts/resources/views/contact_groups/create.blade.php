<div class="modal-body">
    <form action="{{ route('contact-groups.store') }}" method="post">
        @csrf
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Name') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="text" name="name" required />
                </div>
           </div>
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Type') }}</x-form.label>
                    <select class="form-control select" name="type">
                        <option value="customer">Customer</option>
                        <option value="supplier">Supplier</option>
                        <option value="both">Both</option>
                    </select>
                </div>
           </div>
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Calculation Percentage') }}</x-form.label>
                    <x-form.input type="number" name="amount" step="0.01" />
                    <small class="text-muted">Percentage calculation for selling price group or similar logic.</small>
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
