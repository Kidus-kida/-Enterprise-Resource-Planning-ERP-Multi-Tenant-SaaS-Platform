<div class="modal-body">
  <form action="{{ action([\Modules\Products\Http\Controllers\CategoryController::class, 'store']) }}" method="post"
    id="category_add_form">
    @csrf

    <div class="mb-3">
      <label for="name" class="form-label">Category Name:*</label>
      <input type="text" name="name" class="form-control" required placeholder="Category Name">
    </div>

    <div class="mb-3">
      <label for="short_code" class="form-label">Category Code:</label>
      <input type="text" name="short_code" class="form-control" placeholder="Category Code">
      <div class="form-text">Category code is same as <b>HSN code</b></div>
    </div>

    @if(!empty($parent_categories))
      <div class="mb-3">
        <div class="form-check">
          <input type="checkbox" name="add_as_sub_cat" value="1" class="form-check-input toggler"
            data-toggle_id="parent_cat_div" data-toggle_class="parent_cat_div" id="add_as_sub_cat">
          <label class="form-check-label" for="add_as_sub_cat">Add as sub-category</label>
        </div>
      </div>
      <div class="mb-3 d-none" id="parent_cat_div">
        <label for="parent_id" class="form-label">Select Parent Category:</label>
        <select name="parent_id" class="form-select select2" style="width: 100%">
          <option value="">Please Select</option>
          @foreach($parent_categories as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>
    @endif

    @if($account_access)
      <div class="mb-3 add_related_account">
        <label for="add_related_account" class="form-label add_related_account_label">Add Related Account in Category or
          Sub-category Level:</label>
        <select name="add_related_account" id="add_related_account" class="form-select select2" style="width: 100%">
          <option value="">Please Select</option>
          <option value="category_level">Category Level</option>
          <option value="sub_category_level">Sub Category Level</option>
        </select>
      </div>


      <div class="mb-3 cogs_account">
        <label for="cogs_account_id" class="form-label cogs_account_label">COGS Accounts:</label>
        <select name="cogs_account_id" class="form-select select2" style="width: 100%">
          <option value="">Please Select</option>
          @foreach($cogs_accounts as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3 sales_income_account">
        <label for="sales_income_account_id" class="form-label sales_income_account_label">Sales Income Accounts:</label>
        <select name="sales_income_account_id" class="form-select select2" style="width: 100%">
          <option value="">Please Select</option>
          @foreach($sale_income_accounts as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <div class="form-check">
          <input type="checkbox" name="weight_excess_loss_applicable" value="1" class="form-check-input toggler"
            data-toggle_id="weight_excess_loss_applicable" id="weight_excess_loss_applicable">
          <label class="form-check-label" for="weight_excess_loss_applicable">Weight Loss Excess Applicable</label>
        </div>
      </div>

      <div class="mb-3 weight_loss_expense_account weight_excess_loss_applicable_field d-none">
        <label for="weight_loss_expense_account_id" class="form-label weight_loss_expense_account_label">Weight Loss
          Expense Account:</label>
        <select name="weight_loss_expense_account_id" class="form-select select2" style="width: 100%">
          <option value="">Please Select</option>
          @foreach($expense_accounts as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3 weight_excess_income_account weight_excess_loss_applicable_field d-none">
        <label for="weight_excess_income_account_id" class="form-label weight_excess_income_account_label">Weight Excess
          Income Account:</label>
        <select name="weight_excess_income_account_id" class="form-select select2" style="width: 100%">
          <option value="">Please Select</option>
          @foreach($income_accounts as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>
    @endif

    <div class="mb-3 d-none parent_cat_div">
      <label for="price_reduction_acc" class="form-label">Price Reduction Account:</label>
      <select name="price_reduction_acc" class="form-select select2" style="width: 100%">
        <option value="">Please Select</option>
        @foreach($expense_accounts as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3 d-none parent_cat_div">
      <label for="price_increment_acc" class="form-label">Price Increment Account:</label>
      <select name="price_increment_acc" class="form-select select2" style="width: 100%">
        <option value="">Please Select</option>
        @foreach($income_accounts as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label for="vat_exempted" class="form-label">VAT Exempted Products:*</label>
      <select name="vat_exempted" class="form-select select2 vat_exempted">
        <option value="">Please Select</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
      </select>
    </div>

    <div class="mb-3 vat_fields">
      <label for="vat_based_on" class="form-label">VAT Based On:*</label>
      <select name="vat_based_on" class="form-select select2 vat_based_on">
        <option value="">Please Select</option>
        <option value="vat_not_applicable">VAT Not Applicable</option>
        <option value="sale_price">Sale Price</option>
        <option value="profit">Profit</option>
        <option value="profit_percentage">Profit Percentage</option>
      </select>
    </div>

    <div class="mb-3 vat_fields profit_percentage">
      <label for="profit_percentage" class="form-label">Profit Percentage:*</label>
      <input type="text" name="profit_percentage" class="form-control profit_percentage"
        placeholder="Profit Percentage">
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Save</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>

  </form>
</div>

<script>
  $('.select2').select2({
    dropdownParent: $('.category_modal')
  });

  $('#weight_excess_loss_applicable').change(function () {
    if ($(this).prop('checked')) {
      $('.weight_excess_loss_applicable_field').removeClass('d-none');
    } else {
      $('.weight_excess_loss_applicable_field').addClass('d-none');
    }
  })

  $('#add_related_account').change(function () {
    if ($(this).val() === 'sub_category_level') {
      $('.cogs_account').addClass('d-none');
      $('.sales_income_account').addClass('d-none');
    } else {
      $('.cogs_account').removeClass('d-none');
      $('.sales_income_account').removeClass('d-none');
    }
  })

  $(document).on('change', '.vat_exempted', function () {
    if ($(this).val() == 'Yes') {
      $(".vat_fields").hide();
    } else {
      $(".vat_fields").show();
    }
  });

  $(document).on('change', '.vat_based_on', function () {
    if ($(this).val() == 'profit_percentage') {
      $(".profit_percentage").show();
    } else {
      $(".profit_percentage").hide();
    }
  });

  // Re-bind toggler for Add as sub-category
  $(document).on('change', '.toggler', function () {
    var toggle_id = $(this).data('toggle_id');
    if ($(this).is(':checked')) {
      $('#' + toggle_id).removeClass('d-none');
      $('.' + toggle_id).removeClass('d-none');
    } else {
      $('#' + toggle_id).addClass('d-none');
      $('.' + toggle_id).addClass('d-none');
    }
  });

</script>