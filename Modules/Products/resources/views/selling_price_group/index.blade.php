@extends('layouts.app')
@section('title', 'Selling Price Group')

@section('content')



<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Selling Price Group</h4>
                <ul class="breadcrumbs pull-left" style="margin-top: 15px">
                    <li><a href="#">Products</a></li>
                    <li><span>Selling Price Group</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content main-content-inner">
    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>  
        </div>     
    @endif
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Import/Export Selling Price Group Prices'])
            <div class="row">
                <div class="col-sm-6">
                    <a href="{{action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'export'])}}" class="btn btn-primary">Export Selling Price Group Prices</a>
                </div>
                <div class="col-sm-6">
                    {!! Form::open(['url' => action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'import']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="form-group">
                        {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                        {!! Form::file('product_group_prices', ['required' => 'required']); !!}
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="col-sm-12">
                    <h4>@lang('lang_v1.instructions'):</h4>
                    <p>
                        &bull; @lang('lang_v1.price_group_import_istruction')
                    </p>
                    <p>
                        &bull; @lang('lang_v1.price_group_import_istruction1')
                    </p>
                    <p>
                        &bull; @lang('lang_v1.price_group_import_istruction2')
                    </p>
                </div>
            </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Selling Price Groups'])
        @slot('tool')
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-primary btn-modal" 
                    data-href="{{action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'create'])}}" 
                    data-container=".view_modal">
                    <i class="fa fa-plus"></i> Add</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="selling_price_group_table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="notexport">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
    
    <div class="modal fade brands_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready( function(){
        
        //selling_price_group_table
        var selling_price_group_table = $('#selling_price_group_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/selling-price-group',
            columnDefs: [ {
                "targets": 2,
                "orderable": false,
                "searchable": false
            } ],
            columns: [
                {data: 'name', name: 'name'},
                {data: 'description', name: 'description'},
                {data: 'action', name: 'action'}
            ],
            @include('layouts.partials.datatable_export_button')
        });

        $(document).on('submit', 'form#selling_price_group_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: "POST",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success: function(result){
                    if(result.success == true){
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        selling_price_group_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', 'button.delete_spg_button', function(){
            swal({
              title: "Are you sure?",
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                selling_price_group_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', 'button.delete_total_activate', function(){
            swal({
              title: "Are you sure?",
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');

                    $.ajax({
                        method: "GET",
                        url: href,
                        dataType: "json",
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                selling_price_group_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

    });
</script>
@endsection
