<div class="col-sm-4">
    <div class="form-group">
        {!! Form::label('company_id', __('lang_v1.company') . ':') !!}
        {!! Form::select('company_id', $companies, !empty($selected_company_id) ? $selected_company_id : (session('user.company_id') ?? null), ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all_companies')]) !!}
    </div>
</div>
