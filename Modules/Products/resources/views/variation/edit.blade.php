<div class="modal-body">
  <form action="{{ route('products.variations.update', [$variation->id]) }}" method="post" id="variation_edit_form"
    class="form-horizontal">
    @csrf
    @method('PUT')

    <div class="row mb-3">
      <label for="name" class="col-sm-3 col-form-label">Variation Name:*</label>
      <div class="col-sm-9">
        <input type="text" name="name" class="form-control" value="{{ $variation->name }}" required
          placeholder="Variation Name">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-sm-3 col-form-label">Variation values:*</label>
      <div class="col-sm-7">
        @foreach($variation->values as $value)
          <input type="text" name="edit_variation_values[{{ $value->id }}]" class="form-control mb-2"
            value="{{ $value->name }}" required>
        @endforeach
      </div>
      <div class="col-sm-2">
        <button type="button" class="btn btn-primary" id="add_variation_values">+</button>
      </div>
    </div>
    <div id="variation_values"></div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Update</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>

  </form>

  <script>
    $(document).ready(function () {
      $('#variation_edit_form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        $btn.attr('disabled', true);

        $.ajax({
          method: 'POST',
          url: $form.attr('action'),
          dataType: 'json',
          data: $form.serialize(),
          success: function (result) {
            if (result.success === true) {
              $('#generalModalPopup').modal('hide');
              $('.variation_modal').modal('hide');

              if (typeof Toastify !== 'undefined') {
                Toastify({ text: result.msg, className: "success", }).showToast();
              } else if (typeof toastr !== 'undefined') {
                toastr.success(result.msg);
              } else {
                alert(result.msg);
              }

              if (window.variation_table) {
                window.variation_table.ajax.reload();
              } else if ($.fn.DataTable.isDataTable('#variation_table')) {
                $('#variation_table').DataTable().ajax.reload();
              } else {
                location.reload();
              }
            } else {
              alert(result.msg || 'Something went wrong');
              $btn.attr('disabled', false);
            }
          },
          error: function (xhr) {
            alert('Error: ' + xhr.statusText);
            $btn.attr('disabled', false);
          }
        });
      });

      $('#add_variation_values').on('click', function () {
        var html = '<div class="row mb-3">' +
          '<div class="col-sm-7 offset-sm-3">' +
          '<input type="text" name="variation_values[]" class="form-control" required placeholder="Value">' +
          '</div>' +
          '</div>';
        $('#variation_values').append(html);
      });
    });
  </script>
</div>