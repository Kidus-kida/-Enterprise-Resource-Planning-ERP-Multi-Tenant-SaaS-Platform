<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ action([\Modules\Products\Http\Controllers\UnitController::class, 'update'], [$unit->id]) }}"
      method="post" id="unit_edit_form">
      @csrf
      @method('PUT')

      <div class="modal-header">
        <h4 class="modal-title">@lang('Edit Unit')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="form-group col-sm-12 mb-3">
            <label for="actual_name">@lang('Name') :*</label>
            <input type="text" name="actual_name" value="{{ $unit->actual_name }}" class="form-control" required
              placeholder="@lang('Name')" id="actual_name">
          </div>

          <div class="form-group col-sm-12 mb-3">
            <label for="short_name">@lang('Short Name') :*</label>
            <input type="text" name="short_name" value="{{ $unit->short_name }}" class="form-control"
              placeholder="@lang('Short Name')" required id="short_name">
          </div>

          <div class="form-group col-sm-12 mb-3">
            <label for="allow_decimal">@lang('Allow Decimal') :*</label>
            <select name="allow_decimal" class="form-control" required id="allow_decimal">
              <option value="">@lang('Please Select')</option>
              <option value="1" @if($unit->allow_decimal == 1) selected @endif>@lang('Yes')</option>
              <option value="0" @if($unit->allow_decimal == 0) selected @endif>@lang('No')</option>
            </select>
          </div>
          <div class="form-group col-sm-12 mb-3">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="define_base_unit" value="1" class="toggler"
                    data-toggle_id="base_unit_div" @if(!empty($unit->base_unit_id)) checked @endif>
                  @lang('lang_v1.add_as_multiple_of_base_unit')
                </label>
                @show_tooltip(__('lang_v1.multi_unit_help'))
              </div>
            </div>
          </div>
          <div class="form-group col-sm-12 @if(empty($unit->base_unit_id)) hide @endif" id="base_unit_div">
            @if($sale_module)
              <div class="col-md-12">
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="show_in_add_product_unit" value="1" class="input-icheck"
                        @if($unit->show_in_add_product_unit) checked @endif>
                      @lang('Show in Add Product Unit selection dropdown')</label>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="show_in_add_pos_unit" value="1" class="input-icheck"
                        @if($unit->show_in_add_pos_unit) checked @endif>
                      @lang('Show in Add Pos Unit selection dropdown')</label>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="show_in_add_sale_unit" value="1" class="input-icheck"
                        @if($unit->show_in_add_sale_unit) checked @endif>
                      @lang('Show in Add Sales Unit selection dropdown')</label>
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
                      <input type="checkbox" name="show_in_add_project_unit" value="1" class="input-icheck"
                        @if($unit->show_in_add_project_unit) checked @endif>
                      @lang('Show in Add Project Unit selection dropdown')</label>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="show_in_sell_land_block_unit" value="1" class="input-icheck"
                        @if($unit->show_in_sell_land_block_unit) checked @endif>
                      @lang('Show in Sell Land Block Dashboard selection dropdown')</label>
                  </div>
                </div>
              </div>
            @endif
            <table class="table">
              <tr>
                <th style="vertical-align: middle;">1 <span id="unit_name">{{$unit->actual_name}}</span></th>
                <th style="vertical-align: middle;">=</th>
                <td style="vertical-align: middle;">
                  <input type="text" name="base_unit_multiplier" class="form-control input_number"
                    placeholder="@lang('lang_v1.times_base_unit')"
                    value="{{ !empty($unit->base_unit_multiplier) ? @number_format($unit->base_unit_multiplier) : null }}">
                </td>
                <td style="vertical-align: middle;">
                  <select name="base_unit_id" class="form-control">
                    <option value="">@lang('lang_v1.select_base_unit')</option>
                    @foreach($units as $key => $value)
                      <option value="{{ $key }}" @if($unit->base_unit_id == $key) selected @endif>{{ $value }}</option>
                    @endforeach
                  </select>
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
        <button type="submit" class="btn btn-primary">@lang('Update')</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
      </div>

    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->