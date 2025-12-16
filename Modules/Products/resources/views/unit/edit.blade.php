<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Products\Http\Controllers\UnitController::class, 'update'], [$unit->id]), 'method' => 'PUT', 'id' => 'unit_edit_form'
    ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
          aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Edit Unit</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="form-group col-sm-12">
          {!! Form::label('actual_name', 'Name' . ':*') !!}
          {!! Form::text('actual_name', $unit->actual_name, ['class' => 'form-control', 'required', 'placeholder' => 'Name']); !!}
        </div>

        <div class="form-group col-sm-12">
          {!! Form::label('short_name', 'Short Name' . ':*') !!}
          {!! Form::text('short_name', $unit->short_name, ['class' => 'form-control', 'placeholder' => 'Short Name', 'required']); !!}
        </div>

        <div class="form-group col-sm-12">
          {!! Form::label('allow_decimal', 'Allow Decimal' . ':*') !!}
          {!! Form::select('allow_decimal', ['1' => __('messages.yes'), '0' => __('messages.no')], $unit->allow_decimal,
          ['placeholder' => __( 'messages.please_select' ), 'required', 'class' => 'form-control']); !!}
        </div>
        <div class="form-group col-sm-12">
          <div class="form-group">
            <div class="checkbox">
              <label>
                {!! Form::checkbox('define_base_unit', 1, !empty($unit->base_unit_id),[ 'class' => 'toggler',
                'data-toggle_id' => 'base_unit_div' ]); !!} Add as multiple of other unit
              </label>
            </div>
          </div>
        </div>
        <div class="form-group col-sm-12 @if(empty($unit->base_unit_id)) hide @endif" id="base_unit_div">
          @if($sale_module)
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_product_unit', 1, $unit->show_in_add_product_unit, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_product_unit')</label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_pos_unit', 1, $unit->show_in_add_pos_unit, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_pos_unit')</label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_add_sale_unit', 1, $unit->show_in_add_sale_unit, ['class' => 'input-icheck']); !!}
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
                  {!! Form::checkbox('show_in_add_project_unit', 1, $unit->show_in_add_project_unit, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_add_project_unit')</label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  {!! Form::checkbox('show_in_sell_land_block_unit', 1, $unit->show_in_sell_land_block_unit, ['class' => 'input-icheck']); !!}
                  @lang('unit.show_in_sell_land_block_unit')</label>
              </div>
            </div>
          </div>
          @endif
          <table class="table">
            <tr>
              <th style="vertical-align: middle;">1 <span id="unit_name">{{$unit->actual_name}}</span></th>
              <th style="vertical-align: middle;">=</th>
              <td style="vertical-align: middle;">
                {!! Form::text('base_unit_multiplier', !empty($unit->base_unit_multiplier) ?
                @number_format($unit->base_unit_multiplier) : null, ['class' => 'form-control input_number',
                'placeholder' => 'Times of base unit']); !!}</td>
              <td style="vertical-align: middle;">
                {!! Form::select('base_unit_id', $units, $unit->base_unit_id, ['placeholder' => __(
                'lang_v1.select_base_unit' ), 'class' => 'form-control']); !!}
              </td>
            </tr>
            <tr>
              <td colspan="4" style="padding-top: 0;">
                <p class="help-block">*@lang('lang_v1.edit_multi_unit_help_text')</p>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Update</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->