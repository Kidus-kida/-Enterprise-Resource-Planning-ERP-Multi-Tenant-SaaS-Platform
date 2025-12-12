<div class="modal-body">
    <form action="{{ route('contact-groups.update', $contact_group->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Name') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="text" name="name" value="{{ $contact_group->name }}" required />
                </div>
           </div>
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Type') }}</x-form.label>
                    <select class="form-control select" name="type">
                        <option value="customer" {{ $contact_group->type == 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="supplier" {{ $contact_group->type == 'supplier' ? 'selected' : '' }}>Supplier</option>
                        <option value="both" {{ $contact_group->type == 'both' ? 'selected' : '' }}>Both</option>
                    </select>
                </div>
           </div>
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Calculation Percentage') }}</x-form.label>
                    <x-form.input type="number" name="amount" value="{{ $contact_group->amount }}" step="0.01" />
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
