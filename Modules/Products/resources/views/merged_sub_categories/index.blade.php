@extends('layouts.app')
@section('title', 'Merged Sub Categories')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Merged Sub Categories
        <small>Manage your Merged Sub Categories</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Merged Sub Categories'])
        @can('category.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'create'])}}" 
                    data-container=".category_modal">
                    <i class="fa fa-compress"></i> Merge Sub Categories</button>
                </div>
            @endslot
        @endcan
        @can('category.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="merged_sub_category_table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Category</th>
                            <th>Merged Sub Category Name</th>
                            <th>Merged Sub Categories</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade category_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script>
    $(document).on('click', 'button.add_merged_sub_category', function() {
        $.ajax({
            method: 'post',
            url: "{{action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'store'])}}",
            data: { 
                date_and_time : $('#date_and_time').val(),
                merged_sub_category_name : $('#merged_sub_category_name').val(),
                category_id : $('#category').val(),
                sub_categories : $('#sub_categories').val(),
                status : $('#status').val()
             },
            success: function(result) {
                if(result.success == 1){
                    toastr.success(result.msg);
                }else{
                    toastr.success(result.msg);
                }
                merged_sub_category_table.ajax.reload();
                $('.category_modal').modal('hide');
            },
        });
    });
    $(document).on('click', 'button.edit_merged_sub_category', function() {
        $.ajax({
            method: 'put',
            url: "/merged-sub-category/"+$('#merge_id').val(),
            data: { 
                date_and_time : $('#date_and_time').val(),
                merged_sub_category_name : $('#merged_sub_category_name').val(),
                category_id : $('#category').val(),
                sub_categories : $('#sub_categories').val(),
                status : $('#status').val()
             },
            success: function(result) {
                if(result.success == 1){
                    toastr.success(result.msg);
                }else{
                    toastr.success(result.msg);
                }
                merged_sub_category_table.ajax.reload();
                $('.category_modal').modal('hide');
            },
        });
    });

    $(document).on('click', 'a.delete_merge_button', function(e) {
		var page_details = $(this).closest('div.page_details')
		e.preventDefault();
        swal({
            title: "Are you sure?",
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).attr('href');
                var data = $(this).serialize();
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            page_details.remove();
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                        merged_sub_category_table.ajax.reload();
                    },
                });
            }
        });
    });


    var columns = [
            { data: 'date_and_time', name: 'date_and_time' },
            { data: 'category_name', name: 'category_name' },
            { data: 'merged_sub_category_name', name: 'merged_sub_category_name' },
            { data: 'merged_sub_categories', name: 'merged_sub_categories' },
            { data: 'status', name: 'status' },
            { data: 'username', name: 'username' },
            { data: 'action', searchable: false, orderable: false },
        ];
  
        merged_sub_category_table = $('#merged_sub_category_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{action([\Modules\Products\Http\Controllers\MergedSubCategoryController::class, 'index'])}}',
        columnDefs: [ {
            "targets": 6,
            "orderable": false,
            "searchable": false
        } ],
        columns: columns,
        fnDrawCallback: function(oSettings) {
        
        },
    });
</script>
@endsection
