<form
  action="{{ action([\Modules\Products\Http\Controllers\SellingPriceGroupController::class, 'update'], [$spg->id]) }}"
  method="post" id="selling_price_group_form">
  @csrf
  @method('PUT')

  <div class="modal-body">
    <div class="form-group mb-3">
      <label for="spg_name_edit">Name:*</label>
      <input type="text" name="name" value="{{ $spg->name }}" class="form-control" required placeholder="Name"
        id="spg_name_edit" autofocus>
    </div>

    <div class="form-group mb-3">
      <label for="spg_description_edit">Description:</label>
      <textarea name="description" class="form-control" placeholder="Description" rows="3"
        id="spg_description_edit">{{ $spg->description }}</textarea>
    </div>
  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">Update</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
  </div>

</form>

<script>
  // Focus on the name field when modal opens
  $(document).ready(function () {
    setTimeout(function () {
      $('#spg_name_edit').focus();
    }, 500);
  });
</script>