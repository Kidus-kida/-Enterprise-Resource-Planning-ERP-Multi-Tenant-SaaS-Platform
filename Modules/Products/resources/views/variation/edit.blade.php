<div class="modal-body">
    <form action="{{ action([\Modules\Products\Http\Controllers\VariationTemplateController::class, 'update'], [$variation->id]) }}" method="post" id="variation_edit_form" class="form-horizontal">
      @csrf
      @method('PUT')
      
      <div class="row mb-3">
        <label for="name" class="col-sm-3 col-form-label">Variation Name:*</label>
        <div class="col-sm-9">
          <input type="text" name="name" value="{{ $variation->name }}" class="form-control" required placeholder="Variation Name">
        </div>
      </div>
      
      <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Add variation values:*</label>
        @foreach( $variation->values as $attr)
          @if( $loop->first )
            <div class="col-sm-7">
              <input type="text" name="edit_variation_values[{{$attr->id}}]" value="{{$attr->name}}" class="form-control" required>
            </div>
          @endif
        @endforeach
        <div class="col-sm-2">
          <button type="button" class="btn btn-primary" id="add_variation_values">+</button>
        </div>
      </div>
      
      <div id="variation_values">
        @foreach( $variation->values as $attr)
          @if( !$loop->first )
            <div class="row mb-3">
              <div class="col-sm-7 offset-sm-3">
                <input type="text" name="edit_variation_values[{{$attr->id}}]" value="{{$attr->name}}" class="form-control" required>
              </div>
            </div>
          @endif
        @endforeach
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </form>
</div>