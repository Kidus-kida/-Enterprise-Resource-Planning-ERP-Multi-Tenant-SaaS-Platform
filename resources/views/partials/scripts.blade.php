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
