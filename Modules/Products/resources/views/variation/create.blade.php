<div class="modal-body">
  <form action="{{ route('products.variations.store') }}" method="post" id="variation_add_form" class="form-horizontal">
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

  <script>
    $(document).ready(function () {
      $('#add_variation_values').on('click', function () {
        var html = '<div class="row mb-3 variation_row">' +
          '<label class="col-sm-3 col-form-label"></label>' +
          '<div class="col-sm-7">' +
          '<input type="text" name="variation_values[]" class="form-control" required>' +
          '</div>' +
          '<div class="col-sm-2">' +
          '<button type="button" class="btn btn-danger remove_variation_row">-</button>' +
          '</div>' +
          '</div>';
        $('#variation_values').append(html);
      });

      $(document).on('click', '.remove_variation_row', function () {
        $(this).closest('.variation_row').remove();
      });

      $('#variation_add_form').on('submit', function (e) {
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
              if ($('#generalModalPopup').length) {
                $('#generalModalPopup').modal('hide');
              }
              $('.variation_modal').modal('hide');

              if (typeof Toastify !== 'undefined') {
                Toastify({ text: result.msg, className: "success", }).showToast();
              } else {
                alert(result.msg);
              }

              if (window.variation_table && typeof window.variation_table.ajax === 'object') {
                window.variation_table.ajax.reload();
              } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#variation_table')) {
                $('#variation_table').DataTable().ajax.reload();
              } else {
                window.location.reload();
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
    });
  </script>
</div>