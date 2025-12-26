<div class="modal-body">
    <form action="{{ route('customer-statements.store') }}" method="post">
        @csrf
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Customer') }} <span class="text-danger">*</span></x-form.label>
                    <select class="form-control select" name="customer_id" required>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
           
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Date From') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="date" name="date_from" value="{{ date('Y-m-01') }}" required />
                </div>
           </div>
           <div class="col-md-6">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Date To') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="date" name="date_to" value="{{ date('Y-m-t') }}" required />
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Generate') }}</x-form.button>
        </div>
    </form>
</div>
