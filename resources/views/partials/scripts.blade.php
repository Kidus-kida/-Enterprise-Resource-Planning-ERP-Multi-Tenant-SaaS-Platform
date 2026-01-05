@vite([
    'resources/js/app.js',
    'resources/assets/js/bootstrap.bundle.min.js',
    'resources/assets/js/jquery.slimscroll.min.js',
    'resources/assets/plugins/jquery-repeater/jquery.repeater.min.js',
    'resources/assets/js/app.js',
]
)

@livewireScriptConfig 
@yield('vendor-scripts')

<!-- All jQuery-dependent scripts must load AFTER Vite completes -->
<script>
// Wait for Vite module to load jQuery
window.addEventListener('load', function() {
    // Load CSS for bootstrap-fileinput
    var fileinputCSS = document.createElement('link');
    fileinputCSS.rel = 'stylesheet';
    fileinputCSS.href = '{{ asset("js/plugins/bootstrap-fileinput/fileinput.min.css") }}';
    document.head.appendChild(fileinputCSS);
    
    // Load scripts that depend on jQuery
    var scripts = [
        // DataTables and dependencies
        { src: 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', id: 'datatables-js' },
        { src: 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', id: 'datatables-bs5-js' },
        { src: 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js', id: 'datatables-buttons-js' },
        { src: 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js', id: 'datatables-buttons-bs5-js' },
        { src: 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', id: 'jszip-js' },
        { src: 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js', id: 'pdfmake-js' },
        { src: 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js', id: 'pdfmake-fonts-js' },
        { src: 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js', id: 'datatables-html5-js' },
        { src: 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js', id: 'datatables-print-js' },
        
        // Select2
        { src: 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', id: 'select2-js' },
        
        // DateRangePicker (requires moment.js)
        { src: 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', id: 'moment-js' },
        { src: 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', id: 'daterangepicker-js' },
        
        // App scripts
        { src: '{{ asset("js/accounting.min.js") }}', id: 'accounting-js' },
        { src: '{{ asset("js/helpers.js") }}', id: 'helpers-js' },
        { src: '{{ asset("js/jquery.validate.min.js") }}', id: 'validate-js' },
        { src: '{{ asset("js/plugins/bootstrap-fileinput/fileinput.min.js") }}', id: 'fileinput-js' },
        { src: '{{ asset("js/custom.js") }}', id: 'custom-js' },
        { src: '{{ asset("js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js") }}', id: 'datetimepicker-js' }
    ];
    
    scripts.forEach(function(scriptInfo) {
        if (!document.getElementById(scriptInfo.id)) {
            var script = document.createElement('script');
            script.id = scriptInfo.id;
            script.src = scriptInfo.src;
            document.body.appendChild(script);
        }
    });
});
</script>

@stack('page-scripts')
<script type="module">
    @if(count($errors) > 0)
        @foreach($errors->all() as $error)
            Toastify({
                text: "{{ $error }}",
                className: "danger",
            }).showToast();
        @endforeach
    @endif
    @if(Session::has('message'))
        var type = "{{ Session::get('alert-type', '') }}";
        switch (type) {
            case 'info':
                Toastify({
                    text: "{{ Session::get('message') }}",
                    className: "info",
                }).showToast();
                break;
            
            case 'success':
                Toastify({
                    text: "{{ Session::get('message') }}",
                    className: "success",
                }).showToast();
                break;
            
            case 'warning':
                Toastify({
                    text: "{{ Session::get('message') }}",
                    className: "warning",
                }).showToast();
                break;
            
            case 'error':
                Toastify({
                    text: "{{ Session::get('message') }}",
                    className: "danger",
                }).showToast();
                break;
            
            case 'danger':
                Toastify({
                    text: "{{ Session::get('message') }}",
                    className: "danger",
                }).showToast();
                break;
            
        }
    @endif
</script>
