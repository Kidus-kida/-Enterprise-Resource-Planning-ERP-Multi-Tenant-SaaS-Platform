<div class="modal-body">
    <form action="{{ action([\Modules\Products\Http\Controllers\BrandController::class, 'store']) }}" method="post" id="brand_add_form">
      @csrf
      
      <div class="mb-3">
        <label for="name" class="form-label">Brand Name:*</label>
        <input type="text" name="name" class="form-control" required placeholder="Brand Name">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Short Description:</label>
        <input type="text" name="description" class="form-control" placeholder="Short Description">
      </div>
      
      @if($is_repair_installed)
          <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="use_for_repair" value="1" id="use_for_repair">
              <label class="form-check-label" for="use_for_repair">
                  Use for repair?
              </label>
          </div>
          
          <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="is_auto_repair" value="1" id="is_auto_repair">
              <label class="form-check-label" for="is_auto_repair">
                  Use for Auto Repair?
              </label>
          </div>
      @endif

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </form>
</div>