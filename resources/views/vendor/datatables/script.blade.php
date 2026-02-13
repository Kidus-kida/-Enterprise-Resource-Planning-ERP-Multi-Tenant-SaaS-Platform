$(function(){
    var tableId = "%1$s";
    var selector = "#" + tableId;

    if ($.fn.DataTable && $.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().destroy();
    }

    window.{{ config('datatables-html.namespace', 'LaravelDataTables') }} = window.{{ config('datatables-html.namespace', 'LaravelDataTables') }} || {};
    window.{{ config('datatables-html.namespace', 'LaravelDataTables') }}[tableId] = $(selector).DataTable(%2$s);
});
@foreach ($scripts as $script)
@include($script)
@endforeach
