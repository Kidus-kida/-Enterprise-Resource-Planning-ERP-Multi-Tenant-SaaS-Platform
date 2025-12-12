<div class="modal-body">
    <form action="{{ route('contacts.store') }}" method="post">
        @csrf
        <div class="row">
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Name') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="text" name="name" required />
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Business Name') }}</x-form.label>
                    <x-form.input type="text" name="supplier_business_name" />
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Type') }} <span class="text-danger">*</span></x-form.label>
                     <select name="type" class="form-control select">
                        @foreach($types as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                     </select>
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Email') }}</x-form.label>
                    <x-form.input type="email" name="email" />
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Mobile') }}</x-form.label>
                    <x-form.input type="text" name="mobile" />
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Tax Number') }}</x-form.label>
                    <x-form.input type="text" name="tax_number" />
                </div>
           </div>
           <div class="col-md-4">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('City') }}</x-form.label>
                    <x-form.input type="text" name="city" />
                </div>
           </div>
           <div class="col-md-4">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('State') }}</x-form.label>
                    <x-form.input type="text" name="state" />
                </div>
           </div>
            <div class="col-md-4">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Country') }}</x-form.label>
                    <x-form.input type="text" name="country" />
                </div>
           </div>
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Address') }}</x-form.label>
                    <x-form.input type="text" name="address" />
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
