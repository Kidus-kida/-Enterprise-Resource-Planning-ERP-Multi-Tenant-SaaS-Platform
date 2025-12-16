<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Products\Http\Controllers\UnitController::class, 'store']), 'method' => 'post', 'id' => $quick_add ?
    'quick_add_unit_form' : 'unit_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
          aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Add Unit</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <input type="hidden" name="is_property" value="{{$is_property}}">
        <div class="form-group col-sm-12">
          {!! Form::label('actual_name', 'Name' . ':*') !!}
          {!! Form::text('actual_name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Name']); !!}
        </div>

        <div class="form-group col-sm-12">
          {!! Form::label('short_name', 'Short Name' . ':*') !!}
          {!! Form::text('short_name', null, ['class' => 'form-control', 'placeholder' => 'Short Name',
          'required']); !!}
        </div>

        <div class="form-group col-sm-12">
          {!! Form::label('allow_decimal', 'Allow Decimal' . ':*') !!}
          {!! Form::select('allow_decimal', ['1' => __('messages.yes'), '0' => __('messages.no')], null, ['placeholder'
          => __( 'messages.please_select' ), 'required', 'class' => 'form-control']); !!}
        </div>
        @if(!$quick_add)
        <div class="form-group col-sm-12">
          <div class="form-group">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('define_base_unit', 1, false,[ 'class' => 'toggler', 'data-toggle_id' =>
                'base_unit_div' ]); !!} Add as multiple of other unit
              </label>
            </div>
          </div>
        </div>
        <div class="form-group col-sm-12 hide" id="base_unit_div">
          @if($sale_module)
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_product_unit', 1, false, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_product_unit')</label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_pos_unit', 1, false, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_pos_unit')</label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_sale_unit', 1, false, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_sale_unit')</label>
              </div>
            </div>
          </div>
          <div class="clearfix"></div>
          @endif
          @if($property_module)
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_project_unit', 1, false, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_project_unit')</label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_sell_land_block_unit', 1, false, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_sell_land_block_unit')</label>
              </div>
            </div>
          </div>
          @endif

          <table class="table">
            <tr>
              <th style="vertical-align: middle;">1 <span id="unit_name">Unit</span></th>
              <th style="vertical-align: middle;">=</th>
              <td style="vertical-align: middle;">
                {!! Form::text('base_unit_multiplier', null, ['class' => 'form-control input_number', 'placeholder' =>
                'Times of base unit']); !!}</td>
              <td style="vertical-align: middle;">
                {!! Form::select('base_unit_id', $units, null, ['placeholder' => __( 'lang_v1.select_base_unit' ),
                'class' => 'form-control']); !!}
              </td>
            </tr>
          </table>
        </div>
        @endif
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Save</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->