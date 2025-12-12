@extends('layouts.app')

@section('page-content')
<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ isset($asset) ? __('Edit Fixed Asset') : __('Add Fixed Asset') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="assetForm" method="POST" action="{{ isset($asset) ? route('fixed-asset.update', $asset->id) : route('fixed-asset.store') }}">
            @csrf
            @if(isset($asset))
                @method('PUT')
            @endif
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Asset Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $asset->name ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Asset Code') }}</label>
                            <input type="text" name="code" class="form-control" value="{{ $asset->code ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Purchase Date') }}</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ $asset->purchase_date ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Purchase Cost') }}</label>
                            <input type="number" step="0.01" name="purchase_cost" class="form-control" value="{{ $asset->purchase_cost ?? '0' }}">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3">{{ $asset->description ?? '' }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ isset($asset) ? __('Update') : __('Create') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
