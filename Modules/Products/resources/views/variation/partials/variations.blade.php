<!-- Main content -->
<section class="content main-content-inner">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'All Variations'])
        @slot('tool')
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-block btn-primary" 
                data-url="{{action([\Modules\Products\Http\Controllers\VariationTemplateController::class, 'create'])}}" 
                data-ajax-modal="true" data-title="Add Variation" data-container=".variation_modal">
                <i class="fa fa-plus"></i> Add</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="variation_table">
                <thead>
                    <tr>
                        <th>Variations</th>
                        <th>Values</th>
                        <th class="notexport">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade variation_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->