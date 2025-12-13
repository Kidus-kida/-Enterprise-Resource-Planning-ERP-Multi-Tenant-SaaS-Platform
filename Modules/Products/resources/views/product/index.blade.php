@extends('layouts.app')
@section('title', 'Products')

@section('page-content')

@php
    $business_id = request()->session()->get('user.business_id');
    $pacakge_details = array();
    $asset_v = env('APP_VERSION', '1');
@endphp

<div class="content container-fluid">
    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">Products</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">
                Products
            </li>
        </ul>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                @can('product.create')
                    <a href="{{action([\Modules\Products\Http\Controllers\ProductController::class, 'create'])}}" class="btn add-btn">
                        <i class="fa-solid fa-plus"></i> Add
                    </a>
                @endcan
                @if($is_admin)
                <a class="btn btn-success" href="{{action([\Modules\Products\Http\Controllers\ProductController::class, 'downloadExcel'])}}">
                    <i class="fa fa-download"></i> Download Excel
                </a>
                @endif
            </div>
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_type">Product Type:</label>
                        <select class="form-control select2" style="width:100%" id="product_list_filter_type" name="type">
                            <option value="">All</option>
                            <option value="single">Single</option>
                            <option value="variable">Variable</option>
                            <option value="combo">Combo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_category_id">Category:</label>
                        <select class="form-control select2 category_id" style="width:100%" id="product_list_filter_category_id" name="category_id">
                            <option value="">All</option>
                            @foreach($categories as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_sub_category_id">Sub Category:</label>
                        <select class="form-control select2 sub_category_id" style="width:100%" id="product_list_filter_sub_category_id" name="sub_category_id">
                            <option value="">All</option>
                            @foreach($sub_categories as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_semi_finished">Semi Finished:</label>
                        <select class="form-control select2 semi_finished" style="width:100%" id="product_list_filter_semi_finished" name="semi_finished">
                            <option value="">All</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_product_id">Products:</label>
                        <select class="form-control select2 product_id" style="width:100%" id="product_list_filter_product_id" name="product_id">
                            <option value="">All</option>
                            @foreach($products as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_unit_id">Unit:</label>
                        <select class="form-control select2" style="width:100%" id="product_list_filter_unit_id" name="unit_id">
                            <option value="">All</option>
                            @foreach($units as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_tax_id">Tax:</label>
                        <select class="form-control select2" style="width:100%" id="product_list_filter_tax_id" name="tax_id">
                            <option value="">All</option>
                            @foreach($taxes as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="product_list_filter_brand_id">Brand:</label>
                        <select class="form-control select2" style="width:100%" id="product_list_filter_brand_id" name="brand_id">
                            <option value="">All</option>
                            @foreach($brands as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3" id="location_filter">
                    <div class="mb-3">
                        <label class="form-label" for="location_id">Business Location:</label>
                        <select class="form-control select2" style="width:100%" id="location_id" name="location_id">
                            <option value="">All</option>
                            @foreach($business_locations as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label" for="active_state">Status:</label>
                        <select class="form-control select2" style="width:100%" id="active_state" name="active_state">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- include module filter -->
                @if(!empty($pos_module_data))
                    @foreach($pos_module_data as $key => $value)
                        @if(!empty($value['view_path']))
                            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                        @endif
                    @endforeach
                @endif

                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="not_for_selling" value="1" class="form-check-input input-icheck" id="not_for_selling">
                        <label class="form-check-label" for="not_for_selling"><strong>Not For Selling</strong></label>
                    </div>
                </div>
                @if($is_woocommerce)
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="woocommerce_enabled" value="1" class="form-check-input input-icheck" id="woocommerce_enabled">
                            <label class="form-check-label" for="woocommerce_enabled">Woocommerce Enabled</label>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('product.view')
        <div class="card">
            <div class="card-body">
                <!-- Custom Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    @if((array_key_exists('products_all_products',$pacakge_details) && !empty($pacakge_details['products_all_products'])) || !array_key_exists('products_all_products',$pacakge_details) )
                        <li class="nav-item">
                            <a class="nav-link active" href="#product_list_tab" data-bs-toggle="tab">
                                <i class="fa fa-cubes" aria-hidden="true"></i> All Products
                            </a>
                        </li>
                    @endif
                    
                    @if((array_key_exists('products_stock_report',$pacakge_details) && !empty($pacakge_details['products_stock_report'])) || !array_key_exists('products_stock_report',$pacakge_details) )
                        @can('stock_report.view')
                        <li class="nav-item">
                            <a class="nav-link" href="#product_stock_report" data-bs-toggle="tab">
                                <i class="fa fa-hourglass-half" aria-hidden="true"></i> Stock Report
                            </a>
                        </li>
                        @endcan
                    @endif
                </ul>

                <div class="tab-content">
                    @if((array_key_exists('products_all_products',$pacakge_details) && !empty($pacakge_details['products_all_products'])) || !array_key_exists('products_all_products',$pacakge_details) )
                        <div class="tab-pane show active" id="product_list_tab">
                            <div class="table-responsive">
                                @include('products::product.partials.product_list')
                            </div>
                        </div>
                    @endif
                    
                    @if((array_key_exists('products_stock_report',$pacakge_details) && !empty($pacakge_details['products_stock_report'])) || !array_key_exists('products_stock_report',$pacakge_details) )
                        @can('stock_report.view')
                        <div class="tab-pane" id="product_stock_report">
                            {{-- @include('report.partials.stock_report_table') --}}
                <div class="alert alert-warning">Stock Report Table is not available in this version.</div>
                        </div>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    @endcan
</div>

<input type="hidden" id="is_rack_enabled" value="{{$rack_enabled}}">

<div class="modal fade product_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@if($is_woocommerce)
        @include('products::product.partials.toggle_woocommerce_sync_modal')
@endif
    @include('products::product.partials.edit_product_location_modal')

@endsection

@push('page-scripts')
    @vite(["resources/js/datatables.js"])
    <script>
        window.addEventListener('load', function() {
            // Load helpers.js, product.js and opening_stock.js dynamically after jQuery is available
            $.getScript("{{ asset('js/helpers.js') }}", function() {
                $.getScript("{{ asset('js/product.js?v=' . $asset_v) }}", function() {
                    $.getScript("{{ asset('js/opening_stock.js?v=' . $asset_v) }}", function() {
                         initializeProductIndex();
                    });
                });
            });
        });

        function initializeProductIndex() {
            // Language variables for product.js
            window.LANG = {
                sure: "Are you sure?",
                no_row_selected: "No row selected. Please select at least one row.",
                sku_already_exists: "SKU already exists.",
                file_browse_label: "Browse",
                remove: "Remove",
                inc_tax: "Inc. of Tax",
                exc_tax: "Exc. of Tax",
                sp_inc_tax: "Selling Price Inc. Tax",
                sp_exc_tax: "Selling Price Exc. Tax"
            };

            $(document).ready( function(){
            product_table = $('#product_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [[3, 'asc']],
                "ajax": {
                    "url": "/products",
                    "data": function ( d ) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.sub_category_id = $('#product_list_filter_sub_category_id').val();
                        d.product_id = $('#product_list_filter_product_id').val();
                        d.semi_finished = $('#product_list_filter_semi_finished').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.not_for_selling = $('#not_for_selling').is(':checked');
                        d.location_id = $('#location_id').val();
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
                        }

                        if ($('#woocommerce_enabled').length == 1 && $('#woocommerce_enabled').is(':checked')) {
                            d.woocommerce_enabled = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                columnDefs: [ {
                    "targets": [0, 1, 2],
                    "orderable": false,
                    "searchable": false
                } ],
                columns: [
                        { data: 'mass_delete'  },
                        { data: 'image', name: 'products.image'  },
                        { data: 'action', name: 'action'},
                        { data: 'product', name: 'products.name'  },
                        { data: 'product_locations', name: 'product_locations'  },
                        @can('view_purchase_price')
                            { data: 'purchase_price', name: 'max_purchase_price', searchable: false},
                        @endcan
                        @can('access_default_selling_price')
                            { data: 'selling_price', name: 'max_price', searchable: false},
                        @endcan
                        { data: 'current_stock', searchable: false},
                        { data: 'type', name: 'products.type'},
                        { data: 'category', name: 'c1.name'},
                        { data: 'brand', name: 'brands.name'},
                        { data: 'tax', name: 'tax_rates.name', searchable: false},
                        { data: 'sku', name: 'products.sku'},
                        { data: 'semi_finished', name: 'products.semi_finished'},
                        { data: 'product_custom_field1', name: 'products.product_custom_field1', visible: $('#cf_1').text().length > 0  },
                        { data: 'product_custom_field2', name: 'products.product_custom_field2' , visible: $('#cf_2').text().length > 0},
                        { data: 'product_custom_field3', name: 'products.product_custom_field3', visible: $('#cf_3').text().length > 0},
                        { data: 'product_custom_field4', name: 'products.product_custom_field4', visible: $('#cf_4').text().length > 0 },
                    ],
                    createdRow: function( row, data, dataIndex ) {
                        if($('input#is_rack_enabled').val() == 1){
                            var target_col = 0;
                            @can('product.delete')
                                target_col = 1;
                            @endcan
                            $( row ).find('td:eq('+target_col+') div').prepend('<i style="margin:auto;" class="fa fa-plus-circle text-success cursor-pointer no-print rack-details" title="' + LANG.details + '"></i>&nbsp;&nbsp;');
                        }
                        $( row ).find('td:eq(0)').attr('class', 'selectable_td');
                    },
                    fnDrawCallback: function(oSettings) {
                        __currency_convert_recursively($('#product_table'));
                    },
            });
            // Array to track the ids of the details displayed rows
            var detailRows = [];

            $('#product_table tbody').on( 'click', 'tr i.rack-details', function () {
                var i = $(this);
                var tr = $(this).closest('tr');
                var row = product_table.row( tr );
                var idx = $.inArray( tr.attr('id'), detailRows );

                if ( row.child.isShown() ) {
                    i.addClass( 'fa-plus-circle text-success' );
                    i.removeClass( 'fa-minus-circle text-danger' );

                    row.child.hide();
         
                    // Remove from the 'open' array
                    detailRows.splice( idx, 1 );
                } else {
                    i.removeClass( 'fa-plus-circle text-success' );
                    i.addClass( 'fa-minus-circle text-danger' );

                    row.child( get_product_details( row.data() ) ).show();
         
                    // Add to the 'open' array
                    if ( idx === -1 ) {
                        detailRows.push( tr.attr('id') );
                    }
                }
            });

            $('#opening_stock_modal').on('hidden.bs.modal', function(e) {
                product_table.ajax.reload();
            });

            $('table#product_table tbody').on('click', 'a.delete-product', function(e){
                e.preventDefault();
                swal({
                  title: LANG.sure,
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result){
                                if(result.success == true){
                                    toastr.success(result.msg);
                                    product_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#delete-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_rows').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_delete_form').submit();
                        }
                    });
                } else{
                    $('input#selected_rows').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }    
            });

            $(document).on('click', '#deactivate-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_products').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            var form = $('form#mass_deactivate_form')

                            var data = form.serialize();
                                $.ajax({
                                    method: form.attr('method'),
                                    url: form.attr('action'),
                                    dataType: 'json',
                                    data: data,
                                    success: function(result) {
                                        if (result.success == true) {
                                            toastr.success(result.msg);
                                            product_table.ajax.reload();
                                            form
                                            .find('#selected_products')
                                            .val('');
                                        } else {
                                            toastr.error(result.msg);
                                        }
                                    },
                                });
                        }
                    });
                } else{
                    $('input#selected_products').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }    
            })

            $(document).on('click', '#edit-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_products_for_edit').val(selected_rows);
                    $('form#bulk_edit_form').submit();
                } else{
                    $('input#selected_products').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }    
            })

            $('table#product_table tbody').on('click', 'a.activate-product', function(e){
                e.preventDefault();
                var href = $(this).attr('href');
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);
                            product_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change', '#product_list_filter_product_id,#product_list_filter_semi_finished,#product_list_filter_type, #product_list_filter_category_id,#product_list_filter_sub_category_id, #product_list_filter_brand_id, #product_list_filter_unit_id, #product_list_filter_tax_id, #location_id, #active_state, #repair_model_id', 
                function() {
                    if ($("#product_list_tab").hasClass('active')) {
                        product_table.ajax.reload();
                    }

                    if ($("#product_stock_report").hasClass('active')) {
                        stock_report_table.ajax.reload();
                    }
                    
                    if($('#product_list_filter_product_id').val() !== '' && $('#product_list_filter_product_id').val() !== undefined){
                      $('.product').text($('#product_list_filter_product_id :selected').text());
                    }else{
                      $('.product').text('All');
                    }
                    if($('#product_list_filter_category_id').val() !== '' && $('#product_list_filter_category_id').val() !== undefined){
                      $('.category').text($('#product_list_filter_category_id :selected').text());
                    }else{
                      $('.category').text('All');
                    }
                    if($('#product_list_filter_sub_category_id').val() !== '' && $('#product_list_filter_sub_category_id').val() !== undefined){
                      $('.sub_category').text($('#product_list_filter_sub_category_id :selected').text());
                    }else{
                      $('.sub_category').text('All');
                    }
            });

            $(document).on('ifChanged', '#not_for_selling, #woocommerce_enabled', function(){
                if ($("#product_list_tab").hasClass('active')) {
                    product_table.ajax.reload();
                }

                if ($("#product_stock_report").hasClass('active')) {
                    stock_report_table.ajax.reload();
                }
            });

            $('#product_location').select2({dropdownParent: $('#product_location').closest('.modal')});

            @if($is_woocommerce)
                $(document).on('click', '.toggle_woocomerce_sync', function(e){
                    e.preventDefault();
                    var selected_rows = getSelectedRows();
                    if(selected_rows.length > 0){
                        $('#woocommerce_sync_modal').modal('show');
                        $("input#woocommerce_products_sync").val(selected_rows);
                    } else{
                        $('input#selected_products').val('');
                        swal('@lang("lang_v1.no_row_selected")');
                    }    
                });

                $(document).on('submit', 'form#toggle_woocommerce_sync_form', function(e){
                    e.preventDefault();
                    var url = $('form#toggle_woocommerce_sync_form').attr('action');
                    var method = $('form#toggle_woocommerce_sync_form').attr('method');
                    var data = $('form#toggle_woocommerce_sync_form').serialize();
                    var ladda = Ladda.create(document.querySelector('.ladda-button'));
                    ladda.start();
                    $.ajax({
                        method: method,
                        dataType: "json",
                        url: url,
                        data:data,
                        success: function(result){
                            ladda.stop();
                            if (result.success) {
                                $("input#woocommerce_products_sync").val('');
                                $('#woocommerce_sync_modal').modal('hide');
                                toastr.success(result.msg);
                                product_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                });
            @endif
        });
        
        $('.category_id, .sub_category_id').change(function(){
          var cat = $('#product_list_filter_category_id').val();
          var sub_cat = $('#product_list_filter_sub_category_id').val();
          $.ajax({
            method: 'POST',
            url: '/products/get_sub_categories',
            dataType: 'html',
            data: { cat_id: cat },
            success: function(result) {
                console.log(result);
              if (result) {
                $('#product_list_filter_sub_category_id').html(result);
              }
            },
          });
          $.ajax({
            method: 'POST',
            url: '/products/get_product_category_wise',
            dataType: 'html',
            data: { cat_id: cat , sub_cat_id: sub_cat },
            success: function(result) {
              if (result) {
                $('#product_list_filter_product_id').html(result);
              }
            },
          });
        });

        $(document).on('shown.bs.modal', 'div.view_product_modal, div.view_modal, #view_product_modal', 
            function(){
                var div = $(this).find('#view_product_stock_details');
            if (div.length) {
                // $.ajax({
                //     url: "#",
                //     dataType: 'html',
                //     success: function(result) {
                //         div.html(result);
                //         __currency_convert_recursively(div);
                //     },
                // });
                console.log('Stock Report details disabled: ReportController missing');
            }
            __currency_convert_recursively($(this));
        });
        var data_table_initailized = false;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') == '#product_stock_report') {
                if (!data_table_initailized) {
                    var stock_report_cols = [
                        { data: 'sku', name: 'variations.sub_sku' },
                        { data: 'product', name: 'p.name' },
                        { data: 'variation', name: 'variation' },
                        { data: 'category_name', name: 'c.name' },
                        { data: 'location_name', name: 'l.name' },
                        { data: 'unit_price', name: 'variations.sell_price_inc_tax' },
                        { data: 'stock', name: 'stock', searchable: false },
                    ];
                    if ($('th.stock_price').length) {
                            stock_report_cols.push({ data: 'stock_price', name: 'stock_price', searchable: false });
                            stock_report_cols.push({ data: 'stock_value_by_sale_price', name: 'stock_value_by_sale_price', searchable: false, orderable: false });
                            stock_report_cols.push({ data: 'potential_profit', name: 'potential_profit', searchable: false, orderable: false });
                        }
                        
                    
                        // stock_report_cols.push({ data: 'total_purchased', name: 'total_purchased', searchable: false });
                        stock_report_cols.push({ data: 'total_sold', name: 'total_sold', searchable: false });
                        stock_report_cols.push({ data: 'total_transfered', name: 'total_transfered', searchable: false });
                        stock_report_cols.push({ data: 'total_adjusted', name: 'total_adjusted', searchable: false });
                    
                        if ($('th.current_stock_mfg').length) {
                            stock_report_cols.push({ data: 'total_mfg_stock', name: 'total_mfg_stock', searchable: false });
                        }
                    stock_report_table = $('#stock_report_table').DataTable({
                            processing: true,
                            serverSide: true,
                            scrollY: "75vh",
                            scrollX: true,
                            scrollCollapse: false,
                            fixedHeader: false,
                            ajax: {
                                "url": "/reports/stock-report",
                                "data": function(d) {
                                    d.type = $('#product_list_filter_type').val();
                                    d.product_id = $('#product_list_filter_product_id').val();
                                    d.location_id = $('#location_id').val();
                                    d.category_id = $('#product_list_filter_category_id').val();
                                    d.sub_category_id = $('#product_list_filter_sub_category_id').val();
                                    d.brand_id = $('#product_list_filter_brand_id').val();
                                    d.unit_id = $('#product_list_filter_unit_id').val();
                                    d.tax_id = $('#product_list_filter_tax_id').val();
                                    d.store_id = $('#store_id').val();
                                    d.active_state = $('#active_state').val();
                                    d.only_mfg_products = $('#only_mfg_products').length && $('#only_mfg_products').is(':checked') ? 1 : 0;
                                    
                                    
                                },
                            },
                            columns: stock_report_cols, 
                            fnDrawCallback: function(oSettings) {
                                __currency_convert_recursively($('#stock_report_table'));
                            },
                            "footerCallback": function ( row, data, start, end, display ) {
                                var footer_total_stock = 0;
                                var footer_total_sold = 0;
                                var footer_total_transfered = 0;
                                var total_adjusted = 0;
                                var total_stock_price = 0;
                                var footer_stock_value_by_sale_price = 0;
                                var total_potential_profit = 0;
                                var footer_total_mfg_stock = 0;
                                for (var r in data){
                                    footer_total_stock += $(data[r].stock).data('orig-value') ? 
                                    parseFloat($(data[r].stock).data('orig-value')) : 0;
                    
                                    footer_total_sold += $(data[r].total_sold).data('orig-value') ? 
                                    parseFloat($(data[r].total_sold).data('orig-value')) : 0;
                    
                                    footer_total_transfered += $(data[r].total_transfered).data('orig-value') ? 
                                    parseFloat($(data[r].total_transfered).data('orig-value')) : 0;
                    
                                    total_adjusted += $(data[r].total_adjusted).data('orig-value') ? 
                                    parseFloat($(data[r].total_adjusted).data('orig-value')) : 0;
                    
                                    total_stock_price += $(data[r].stock_price).data('orig-value') ? 
                                    parseFloat($(data[r].stock_price).data('orig-value')) : 0;
                    
                                    footer_stock_value_by_sale_price += $(data[r].stock_value_by_sale_price).data('orig-value') ? 
                                    parseFloat($(data[r].stock_value_by_sale_price).data('orig-value')) : 0;
                    
                                    total_potential_profit += $(data[r].potential_profit).data('orig-value') ? 
                                    parseFloat($(data[r].potential_profit).data('orig-value')) : 0;
                    
                                    footer_total_mfg_stock += $(data[r].total_mfg_stock).data('orig-value') ? 
                                    parseFloat($(data[r].total_mfg_stock).data('orig-value')) : 0;
                                }
                    
                                $('.footer_total_stock').html(__currency_trans_from_en(footer_total_stock, false));
                                $('.footer_total_stock_price').html(__currency_trans_from_en(total_stock_price));
                                $('.footer_total_sold').html(__currency_trans_from_en(footer_total_sold, false));
                                $('.footer_total_transfered').html(__currency_trans_from_en(footer_total_transfered, false));
                                $('.footer_total_adjusted').html(__currency_trans_from_en(total_adjusted, false));
                                $('.footer_stock_value_by_sale_price').html(__currency_trans_from_en(footer_stock_value_by_sale_price));
                                $('.footer_potential_profit').html(__currency_trans_from_en(total_potential_profit));
                                if ($('th.current_stock_mfg').length) {
                                    $('.footer_total_mfg_stock').html(__currency_trans_from_en(footer_total_mfg_stock, false));
                                }
                            },
                        });
                    data_table_initailized = true;
                } else {
                    stock_report_table.ajax.reload();
                }
            } else {
                product_table.ajax.reload();
            }
        });

        $(document).on('click', '.update_product_location', function(e){
            e.preventDefault();
            var selected_rows = getSelectedRows();
            
            if(selected_rows.length > 0){
                $('input#selected_products').val(selected_rows);
                var type = $(this).data('type');
                var modal = $('#edit_product_location_modal');
                if(type == 'add') {
                    modal.find('.remove_from_location_title').addClass('hide');
                    modal.find('.add_to_location_title').removeClass('hide');
                } else if(type == 'remove') {
                    modal.find('.add_to_location_title').addClass('hide');
                    modal.find('.remove_from_location_title').removeClass('hide');
                }

                modal.modal('show');
                modal.find('#product_location').select2({ dropdownParent: modal });
                modal.find('#product_location').val('').change();
                modal.find('#update_type').val(type);
                modal.find('#products_to_update_location').val(selected_rows);
            } else{
                $('input#selected_products').val('');
                swal('@lang("lang_v1.no_row_selected")');
            }    
        });

    $(document).on('submit', 'form#edit_product_location_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success == true) {
                    $('div#edit_product_location_modal').modal('hide');
                    toastr.success(result.msg);
                    product_table.ajax.reload();
                    $('form#edit_product_location_form')
                    .find('button[type="submit"]')
                    .attr('disabled', false);
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    
    $(document).on('submit', 'form#disable_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        console.log($(this).attr('action'));
        
        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success == true) {
                    toastr.success(result.msg);
                    $('form#disable_form')
                    .find('button[type="submit"]')
                    .attr('disabled', false);
                    
                    $('.modal').modal('hide');
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
        }
    </script>
@endpush