<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ action([\Modules\Products\Http\Controllers\UnitController::class, 'store']) }}" method="post"
      id="{{ $quick_add ? 'quick_add_unit_form' : 'unit_add_form' }}">
      @csrf

      <div class="modal-header">
        <h4 class="modal-title">@lang('Add Unit')</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <input type="hidden" name="is_property" value="{{$is_property}}">
          <div class="form-group col-sm-12 mb-3">
            <label for="actual_name">@lang('Name') :*</label>
            <input type="text" name="actual_name" class="form-control" required placeholder="@lang('Name')"
              id="actual_name">
          </div>

          <div class="form-group col-sm-12 mb-3">
            <label for="short_name">@lang('Short Name') :*</label>
            <input type="text" name="short_name" class="form-control" required placeholder="@lang('Short Name')"
              id="short_name">
          </div>

          <div class="form-group col-sm-12 mb-3">
            <label for="allow_decimal">@lang('Allow Decimal') :*</label>
            <select name="allow_decimal" class="form-control" required id="allow_decimal">
              <option value="">@lang('Please Select')</option>
              <option value="1">@lang('Yes')</option>
              <option value="0">@lang('No')</option>
            </select>
          </div>
          @if(!$quick_add)
            <div class="form-group col-sm-12 mb-3">
              <div class="form-group">
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="define_base_unit" value="1" class="toggler"
                      data-toggle_id="base_unit_div">
                    @lang('lang_v1.add_as_multiple_of_base_unit')
                  </label>
                  @if(!empty($help_explanations['add_as_multiple_of_other_unit']))
                    @show_tooltip($help_explanations['add_as_multiple_of_other_unit'])
                  @endif
                </div>
              </div>
            </div>
            <div class="form-group col-sm-12 hide" id="base_unit_div">
              @if($sale_module)
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="show_in_add_product_unit" value="1" class="input-icheck">
                        @lang('Show in Add Product Unit selection dropdown')</label>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="show_in_add_pos_unit" value="1" class="input-icheck">
                        @lang('Show in Add Pos Unit selection dropdown')</label>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="show_in_add_sale_unit" value="1" class="input-icheck">
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
                        <input type="checkbox" name="show_in_add_project_unit" value="1" class="input-icheck">
                        @lang('Show in Add Project Unit selection dropdown')</label>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="show_in_sell_land_block_unit" value="1" class="input-icheck">
                        @lang('Show in Sell Land Block Dashboard selection dropdown')</label>
                    </div>
                  </div>
                </div>
              @endif

              <table class="table">
                <tr>
                  <th style="vertical-align: middle;">1 <span id="unit_name">@lang('product.unit')</span></th>
                  <th style="vertical-align: middle;">=</th>
                  <td style="vertical-align: middle;">
                    <input type="text" name="base_unit_multiplier" class="form-control input_number"
                      placeholder="@lang('lang_v1.times_base_unit')">
                  </td>
                  <td style="vertical-align: middle;">
                    <select name="base_unit_id" class="form-control">
                      <option value="">@lang('lang_v1.select_base_unit')</option>
                      @foreach($units as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                      @endforeach
                    </select>
                  </td>
                </tr>
              </table>
            </div>
          @endif
        </div>

      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang('Save')</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
      </div>

    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->