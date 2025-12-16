@extends('layouts.app')
@section('title', 'Units')

@section('content')


  <div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Manage your Units</h4>
                <ul class="breadcrumbs pull-left" style="margin-top: 15px">
                    <li><a href="#">Units</a></li>
                    <li><span>Manage your Units</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

  <!-- Main content -->
  <section class="content main-content-inner">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All your Units'])
    <input type="hidden" name="is_property" id="is_property" value="0">
    @can('unit.create')
    @slot('tool')
    <div class="box-tools pull-right">
      <button type="button" class="btn btn-primary btn-modal" 
      data-href="{{action([\Modules\Products\Http\Controllers\UnitController::class, 'create'])}}" 
      data-container=".unit_modal">
      <i class="fa fa-plus"></i> Add</button>
    </div>
    @endslot
    @endcan
    @can('unit.view')
    <div class="table-responsive">
      <table class="table table-bordered table-striped" id="unit_table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Short Name</th>
            <th>Allow Decimal @if(!empty($help_explanations['allow_decimal'])) @show_tooltip($help_explanations['allow_decimal']) @endif</th>
            <th>Multiple Units</th>
            <th>Connected Units</th>
            <th class="notexport">Action</th>
          </tr>
        </thead>
      </table>
    </div>
    @endcan
    @endcomponent

    <div class="modal fade unit_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
  </div>

</section>
@stop
@section('javascript')
<!-- /.content -->
<script type="text/javascript">
  
</script>
@endsection
