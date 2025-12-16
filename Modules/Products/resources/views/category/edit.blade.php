<div class="modal-body">
    <form action="{{ action([\Modules\Products\Http\Controllers\CategoryController::class, 'update'], [$category->id]) }}" method="post" id="category_edit_form">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label for="name" class="form-label">Category Name:*</label>
        <input type="text" name="name" value="{{ $category->name }}" class="form-control" required placeholder="Category Name">
      </div>

      <div class="mb-3">
        <label for="short_code" class="form-label">Category Code:</label>
        <input type="text" name="short_code" value="{{ $category->short_code }}" class="form-control" placeholder="Category Code">
        <div class="form-text">Category code is same as <b>HSN code</b></div>
      </div>
      
      @if(!empty($parent_categories))
          <div class="mb-3">
            <div class="form-check">
                 <input type="checkbox" name="add_as_sub_cat" value="1" class="form-check-input toggler" data-toggle_id="parent_cat_div" data-toggle_class="parent_cat_div" id="add_as_sub_cat" @if(!$is_parent) checked @endif>
                 <label class="form-check-label" for="add_as_sub_cat">{{ __( 'category.add_as_sub_category' ) }}</label>
            </div>
          </div>
          <div class="mb-3 @if($is_parent) d-none @endif" id="parent_cat_div">
            <label for="parent_id" class="form-label">{{ __( 'category.select_parent_category' ) }}:</label>
            <select name="parent_id" class="form-select select2" style="width: 100%">
                <option value="">Please Select</option>
                @foreach($parent_categories as $id => $name)
                    <option value="{{ $id }}" @if($id == $selected_parent) selected @endif>{{ $name }}</option>
                @endforeach
            </select>
          </div>
      @endif
      
      @if($account_access)
      <div class="mb-3 add_related_account">
        <label for="add_related_account" class="form-label add_related_account_label">{{ __( 'account.add_related_account' ) }}:</label>
        <select name="add_related_account" id="add_related_account" class="form-select select2" style="width: 100%" required>
            <option value="">Please Select</option>
            <option value="category_level" @if($category->add_related_account == 'category_level') selected @endif>Category Level</option>
            <option value="sub_category_level" @if($category->add_related_account == 'sub_category_level') selected @endif>Sub Category Level</option>
        </select>
      </div>
      
      <div class="mb-3 cogs_account @if($category->add_related_account == 'sub_category_level') d-none @endif">
        <label for="cogs_account_id" class="form-label cogs_account_label">{{ __( 'account.cogs_account' ) }}:</label>
        <select name="cogs_account_id" class="form-select select2" style="width: 100%" required>
            <option value="">Please Select</option>
            @foreach($cogs_accounts as $id => $name)
                <option value="{{ $id }}" @if($id == $category->cogs_account_id) selected @endif>{{ $name }}</option>
            @endforeach
        </select>
      </div>

      <div class="mb-3 sales_income_account @if($category->add_related_account == 'sub_category_level') d-none @endif">
        <label for="sales_income_account_id" class="form-label sales_income_account_label">{{ __( 'account.sales_income_account' ) }}:</label>
        <select name="sales_income_account_id" class="form-select select2" style="width: 100%" required>
            <option value="">Please Select</option>
             @foreach($sale_income_accounts as $id => $name)
                <option value="{{ $id }}" @if($id == $category->sales_income_account_id) selected @endif>{{ $name }}</option>
            @endforeach
        </select>
      </div>

      <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" name="weight_excess_loss_applicable" value="1" class="form-check-input toggler" data-toggle_id="weight_excess_loss_applicable" id="weight_excess_loss_applicable" @if($category->weight_excess_loss_applicable) checked @endif>
            <label class="form-check-label" for="weight_excess_loss_applicable">Weight excess/loss applicable</label>
        </div>
      </div>

      <div class="mb-3 weight_loss_expense_account weight_excess_loss_applicable_field @if(!$category->weight_excess_loss_applicable) d-none @endif">
        <label for="weight_loss_expense_account_id" class="form-label weight_loss_expense_account_label">{{ __( 'lang_v1.weight_loss_expense_account' ) }}:</label>
        <select name="weight_loss_expense_account_id" class="form-select select2" style="width: 100%">
            <option value="">Please Select</option>
            @foreach($expense_accounts as $id => $name)
                <option value="{{ $id }}" @if($id == $category->weight_loss_expense_account_id) selected @endif>{{ $name }}</option>
            @endforeach
        </select>
      </div>
      
      <div class="mb-3 weight_excess_income_account weight_excess_loss_applicable_field  @if(!$category->weight_excess_loss_applicable) d-none @endif">
        <label for="weight_excess_income_account_id" class="form-label weight_excess_income_account_label">{{ __( 'lang_v1.weight_excess_income_account' ) }}:</label>
        <select name="weight_excess_income_account_id" class="form-select select2" style="width: 100%">
            <option value="">Please Select</option>
            @foreach($income_accounts as $id => $name)
                <option value="{{ $id }}" @if($id == $category->weight_excess_income_account_id) selected @endif>{{ $name }}</option>
            @endforeach
        </select>
      </div>
      @endif
      
      <div class="mb-3 @if($is_parent) d-none @endif parent_cat_div">
        <label for="price_reduction_acc" class="form-label">{{ __( 'category.price_reduction_acc' ) }}:</label>
        <select name="price_reduction_acc" class="form-select select2" style="width: 100%">
             <option value="">Please Select</option>
             @foreach($expense_accounts as $id => $name)
                <option value="{{ $id }}" @if($id == $category->price_reduction_acc) selected @endif>{{ $name }}</option>
             @endforeach
        </select>
      </div>
      
      <div class="mb-3 @if($is_parent) d-none @endif parent_cat_div">
        <label for="price_increment_acc" class="form-label">{{ __( 'category.price_increment_acc' ) }}:</label>
        <select name="price_increment_acc" class="form-select select2" style="width: 100%">
             <option value="">Please Select</option>
             @foreach($income_accounts as $id => $name)
                <option value="{{ $id }}" @if($id == $category->price_increment_acc) selected @endif>{{ $name }}</option>
             @endforeach
        </select>
      </div>
      
      <!-- Remaining Stock Adjusts (Commented out in original) -->
        
        <div class="mb-3 @if($is_parent) d-none @endif">
          <label for="vat_exempted" class="form-label">{{ __( 'category.vat_exempted' ) }}:*</label>
          <select name="vat_exempted" class="form-select select2 vat_exempted" required>
              <option value="">Please Select</option>
              <option value="Yes" @if($category->vat_exempted == 'Yes') selected @endif>Yes</option>
              <option value="No" @if($category->vat_exempted == 'No') selected @endif>No</option>
          </select>
        </div>
        
        <div class="mb-3 vat_fields" @if($category->vat_exempted == 'Yes') style="display:none;" @endif>
          <label for="vat_based_on" class="form-label">{{ __( 'category.vat_based_on' ) }}:*</label>
          <select name="vat_based_on" class="form-select select2 vat_based_on" required>
              <option value="">Please Select</option>
              <option value="vat_not_applicable" @if($category->vat_based_on == 'vat_not_applicable') selected @endif>{{ __('category.vat_not_applicable') }}</option>
              <option value="sale_price" @if($category->vat_based_on == 'sale_price') selected @endif>{{ __('category.sale_price') }}</option>
              <option value="profit" @if($category->vat_based_on == 'profit') selected @endif>{{ __('category.profit') }}</option>
              <option value="profit_percentage" @if($category->vat_based_on == 'profit_percentage') selected @endif>{{ __('category.profit_percentage') }}</option>
          </select>
        </div>
        
       <div class="mb-3 vat_fields profit_percentage" @if($category->vat_based_on != 'profit_percentage') style="display:none;" @endif>
          <label for="profit_percentage" class="form-label">{{ __( 'category.profit_percentage' ) }}:*</label>
          <input type="text" name="profit_percentage" value="{{ $category->profit_percentage }}" class="form-control profit_percentage" placeholder="{{ __( 'category.profit_percentage' ) }}">
        </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Update</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>

    </form>
</div>


<script>
  $('.select2').select2({
        dropdownParent: $('.category_modal')
    }); // Added dropdownParent to fix Select2 in standard Bootrap 5 modal

  $('#weight_excess_loss_applicable').change(function () {
    if($(this).prop('checked')){
      $('.weight_excess_loss_applicable_field').removeClass('d-none');
    }else{
      $('.weight_excess_loss_applicable_field').addClass('d-none');
    }
  })
  $('#add_related_account').change(function () {
    if($(this).val() === 'sub_category_level'){
      $('.cogs_account').addClass('d-none'); // d-none for BS5
      $('.sales_income_account').addClass('d-none');
    }else{
      $('.cogs_account').removeClass('d-none');
      $('.sales_income_account').removeClass('d-none');
    }
  })
  
  $(document).on('change', '.vat_exempted', function() {
        if($(this).val() == 'Yes'){
            $(".vat_fields").hide();
        }else{
            $(".vat_fields").show();
        }
    });

    $(document).on('change', '.vat_based_on', function() {
        if($(this).val() == 'profit_percentage'){
            $(".profit_percentage").show();
        }else{
            $(".profit_percentage").hide();
        }
    });

    // Re-bind toggler for Add/Edit as sub-category since classes changed
    $(document).on('change', '.toggler', function() {
        var toggle_id = $(this).data('toggle_id');
        if($(this).is(':checked')){
            $('#' + toggle_id).removeClass('d-none');
            $('.' + toggle_id).removeClass('d-none');
        } else {
            $('#' + toggle_id).addClass('d-none');
            $('.' + toggle_id).addClass('d-none');
        }
    });
</script>