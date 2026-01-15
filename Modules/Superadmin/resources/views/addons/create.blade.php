@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Create Add-on</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.addons.index') }}">Add-ons</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('superadmin.addons.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Add-on Details</h4>
                                    
                                    <div class="form-group">
                                        <label>Add-on Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                            value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Module <span class="text-danger">*</span></label>
                                        <select name="module_id" class="form-control @error('module_id') is-invalid @enderror" required>
                                            <option value="">Select Module</option>
                                            @foreach($modules as $module)
                                                <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                                    {{ $module->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('module_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Price (ETB) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                                            value="{{ old('price', 0) }}" step="0.01" min="0" required>
                                        @error('price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                            rows="3">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Features & Settings</h4>
                                    
                                    <div class="form-group">
                                        <label>Features</label>
                                        <div id="features-container">
                                            <div class="input-group mb-2">
                                                <input type="text" name="features[]" class="form-control" placeholder="Feature name">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-success" onclick="addFeature()">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">Add features included in this add-on</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 100) }}">
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                            value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active</strong>
                                            <br><small class="text-muted">Add-on is available for selection</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Create Add-on</button>
                                <a href="{{ route('superadmin.addons.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

<script>
function addFeature() {
    const container = document.getElementById('features-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="features[]" class="form-control" placeholder="Feature name">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="this.closest('.input-group').remove()">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
}
</script>
@endsection
