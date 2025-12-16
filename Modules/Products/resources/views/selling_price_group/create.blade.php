<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'store']), 'method' => 'post', 'id' => 'selling_price_group_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Selling Price Group</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', 'Name' . ':*') !!}
          {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Name' ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', 'Description' . ':') !!}
          {!! Form::textarea('description', null, ['class' => 'form-control','placeholder' => 'Description', 'rows' => 3]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Save</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->