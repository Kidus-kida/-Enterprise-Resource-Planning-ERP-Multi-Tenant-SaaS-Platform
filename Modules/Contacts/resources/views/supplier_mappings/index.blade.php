@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">

    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Add Supplier Map Products') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Supplier Product Mappings') }}
            </li>
        </ul>
    </x-breadcrumb>
    
    <div class="row">
         <form action="" method="post" id="mapping_form">
             @csrf
             <div class="col-sm-12">
                 <div class="card">
                     <div class="card-body">
                         <div class="row">
                            <div class="col-md-4">
                                <div class="input-block mb-3">
                                    <label for="type">Supplier List</label>
                                    <select class="form-control select2" id="type" name="type" required>
                                        <option value="">{{ __('Select Supplier') }}</option>
                                        @foreach($name as $id => $supplierName)
                                            <option value="{{ $id }}" {{ $type == $id ? 'selected' : '' }}>{{ $supplierName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                         </div>
                         
                         <div class="row mt-4">
                            <!-- Unmapped Products -->
                            <div class="col-md-5">
                                <h5>Unmapped Products <span class="text-danger" id="unmapped_count"></span></h5>
                                <input type="text" id="searchUnmapped" class="form-control mb-2" placeholder="Search unmapped...">
                                <div class="list-group" id="ss_imp_list" style="height: 300px; overflow-y: auto; border: 1px solid #ddd;">
                                    <!-- AJAX loaded items -->
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="col-md-2 text-center d-flex flex-column justify-content-center">
                                <button type="button" id="btn-add" class="btn btn-primary mb-2"> Add &rarr; </button>
                                <button type="button" id="btn-remove" class="btn btn-secondary"> &larr; Remove </button>
                            </div>
                            
                            <!-- Mapped Products -->
                            <div class="col-md-5">
                                <h5>Mapped Products <span class="text-primary" id="mapped_count"></span></h5>
                                <input type="text" id="searchMapped" class="form-control mb-2" placeholder="Search mapped...">
                                <div class="list-group" id="ss_unimp_list" style="height: 300px; overflow-y: auto; border: 1px solid #ddd;">
                                    <!-- AJAX loaded items -->
                                </div>
                            </div>
                         </div>
                         
                         <div class="row mt-4">
                             <div class="col-md-12 text-end">
                                 <button type="button" id="save-mapped" class="btn btn-primary">@lang('Save')</button>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </form>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    
    // Load Mappings on Supplier Change
    $('#type').on('change', function() {
        var supplier_id = $(this).val();
        loadMappings(supplier_id);
    });

    function loadMappings(supplier_id) {
        $('#ss_imp_list').empty();
        $('#ss_unimp_list').empty();
        
        if(!supplier_id) return;

        $.ajax({
            url: '/add-supplier-map-product/get-supplier-mapped',
            type: 'GET',
            data: { supplier_id: supplier_id },
            success: function(response) {
                // response.names = Unmapped
                // response.mappingnames = Mapped
                
                $.each(response.names, function(id, name) {
                    $('#ss_imp_list').append(createListItem(id, name));
                });
                
                $.each(response.mappingnames, function(id, name) {
                    $('#ss_unimp_list').append(createListItem(id, name));
                });
            },
            error: function() {
                alert('Error loading mappings');
            }
        });
    }

    function createListItem(id, name) {
        return `<a href="#" class="list-group-item list-group-item-action" data-id="${id}">${name}</a>`;
    }

    // Selection Logic
    $(document).on('click', '.list-group-item', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
    });

    // Move Items (Right / Add)
    $('#btn-add').on('click', function() {
        var items = $('#ss_imp_list .active');
        items.removeClass('active').appendTo('#ss_unimp_list');
    });

    // Move Items (Left / Remove)
    $('#btn-remove').on('click', function() {
        var items = $('#ss_unimp_list .active');
        items.removeClass('active').appendTo('#ss_imp_list');
    });

    // Filtering
    $('#searchUnmapped').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#ss_imp_list a").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('#searchMapped').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#ss_unimp_list a").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Save
    $('#save-mapped').on('click', function() {
        var supplier_id = $('#type').val();
         if(!supplier_id) {
            alert('Please select a supplier');
            return;
        }
        
        var mappedIds = [];
        $('#ss_unimp_list a').each(function() {
            mappedIds.push($(this).data('id'));
        });

        // The AJAX URL in source was /contacts/create_mappings OR /contacts/mapping.
        // I registered 'create_mappings' in web.php
        $.ajax({
            url: '/create_mappings', 
            type: 'POST', 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                type: supplier_id,
                ss_umimp_list: mappedIds
            },
            success: function(response) {
                if(response.success) {
                    // toastr.success('Saved successfully');
                    alert('Saved successfully'); 
                } else {
                    alert('Error saving: ' + (response.msg ? response.msg : 'Unknown error'));
                }
            },
            error: function() {
                alert('Error saving');
            }
        });
    });

});
</script>
@endpush
