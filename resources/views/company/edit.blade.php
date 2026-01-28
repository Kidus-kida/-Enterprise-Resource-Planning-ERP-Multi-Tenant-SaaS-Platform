<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ action([\App\Http\Controllers\CompanyController::class, 'update'], [$company->id]) }}" method="post" id="company_edit_form">
      @method('PUT')
      @csrf
      <div class="modal-header">
        <h4 class="modal-title">@lang( 'company.edit_company' )</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          <label for="name">@lang( 'company.name' ):*</label>
            <input type="text" class="form-control" required placeholder="@lang( 'company.name' )" name="name" id="name" value="{{ $company->name }}">
        </div>
  
        <div class="form-group">
          <label for="tax_number">@lang( 'company.tax_number' ):</label>
            <input type="text" class="form-control" placeholder="@lang( 'company.tax_number' )" name="tax_number" id="tax_number" value="{{ $company->tax_number }}">
        </div>
      </div>
  
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang( 'messages.close' )</button>
        <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      </div>
  
    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
