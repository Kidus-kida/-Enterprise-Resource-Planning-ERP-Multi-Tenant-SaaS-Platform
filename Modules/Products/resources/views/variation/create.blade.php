<div class="modal-body">
    <form action="{{ action([\Modules\Products\Http\Controllers\VariationTemplateController::class, 'store']) }}" method="post" id="variation_add_form" class="form-horizontal">
      @csrf
      
        <div class="row mb-3">
          <label for="name" class="col-sm-3 col-form-label">Variation Name:*</label>
          <div class="col-sm-9">
            <input type="text" name="name" class="form-control" required placeholder="Variation Name">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Add variation values:*</label>
          <div class="col-sm-7">
             <input type="text" name="variation_values[]" class="form-control" required>
          </div>
          <div class="col-sm-2">
            <button type="button" class="btn btn-primary" id="add_variation_values">+</button>
          </div>
        </div>
        <div id="variation_values"></div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </form>
</div>