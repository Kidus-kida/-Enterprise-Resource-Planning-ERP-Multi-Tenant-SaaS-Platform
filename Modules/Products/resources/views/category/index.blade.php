@extends('layouts.app')
@section('title', 'Categories')

@section('content')


<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Manage your Categories</h4>
                <ul class="breadcrumbs pull-left" style="margin-top: 15px">
                    <li><a href="#">Categories</a></li>
                    <li><span>Manage your Categories</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content main-content-inner">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Manage your Categories'])
        @can('category.create')
            @slot('tool')
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-modal" 
                    data-href="{{action([\Modules\Products\Http\Controllers\CategoryController::class, 'create'])}}" 
                    data-container=".category_modal">
                    <i class="fa fa-plus"></i> Add</button>
                </div>
            @endslot
        @endcan
        @can('category.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="all_category_table" >
                    <thead>
                        <tr>
                            <th style="width:25px">Category</th>
                            <th>Code</th>
                            <th style="width:25px">Sub Category</th>
                            <th>Sub Cat Code</th>
                            <th style="width:50px">Account Group (COGS)</th>
                            <th style="width:50px">Sales Income Account</th>
                            <th>VAT based on</th>
                            <th style="width:150px">Apply VAT on</th>
                            <th>Vat Exempted</th>
                            <!--<th>@lang( 'category.price_reduction_acc' )</th>
                            <th>@lang( 'category.price_increment_acc' )</th>
                            <th>@lang( 'category.remaining_stock_adjusts' )</th>-->
                            <th class="notexport">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade category_modal" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script>
        $(document).ready(function(){
            category_table = $('#all_category_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/categories',
                   
                },
                columns: [
                    { data: 'category_name', name: 'name' },
                    { data: 'category_short_code', name: 'short_code' },
                    { data: 'sub_category_name', name: 'name' },
                    { data: 'sub_category_short_code', name: 'short_code' },
                    { data: 'cogs', name: 'cogs' },
                    { data: 'sales_accounts', name: 'sales_accounts' },
                    { data: 'vat_based_on', name: 'vat_based_on' },
                    { data: 'apply_vat_on', name: 'apply_vat_on' },
                    { data: 'vat_exempted', name: 'vat_exempted' },
                    //{ data: 'decr_accounts', name: 'decr_accounts' },
                    //{ data: 'incr_accounts', name: 'incr_accounts' },
                    //{ data: 'remaining_stock_adjusts', name: 'remaining_stock_adjusts' },
                    { data: 'action', name: 'action' },
                ],
                @include('layouts.partials.datatable_export_button')
              
            });
        })
    </script>
@endsection