<div class="modal-body">
    <form action="{{ route('supplier-mappings.store') }}" method="post">
        @csrf
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Supplier') }} <span class="text-danger">*</span></x-form.label>
                    <select class="form-control select" name="supplier_id" required>
                        @foreach($suppliers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Product') }} <span class="text-danger">*</span></x-form.label>
                    <select class="form-control select" name="product_id" required>
                         @foreach($products as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
