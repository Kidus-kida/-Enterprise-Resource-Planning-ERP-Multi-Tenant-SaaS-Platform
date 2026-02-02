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
// Unified Sidebar Initializer
function initSidebar() {
    // 1. Re-initialize Slimscroll (Styling/Scrolling)
    var $slimScrolls = $('.slimscroll');
    if ($slimScrolls.length > 0) {
        $slimScrolls.slimScroll({
            height: 'auto',
            width: '100%',
            position: 'right',
            size: '7px',
            color: '#ccc',
            wheelStep: 10,
            touchScrollStep: 100
        });
        var wHeight = $(window).height() - 60;
        $slimScrolls.height(wHeight);
        $('.sidebar .slimScrollDiv').height(wHeight);
        $(window).resize(function () {
            var rHeight = $(window).height() - 60;
            $slimScrolls.height(rHeight);
            $('.sidebar .slimScrollDiv').height(rHeight);
        });
    }

    // 2. Handle Event Listeners (Logic)
    var $sidebarMenu = $('#sidebar-menu a');
    
    // Unbind EVERYTHING to start fresh (fixes double-toggle if persisted)
    $sidebarMenu.off('click');
    
    // Bind the toggle logic (fixes paused if swapped)
    $sidebarMenu.on('click', function (e) {
        if ($(this).parent().hasClass('submenu')) {
            e.preventDefault();
        }
        if (!$(this).hasClass('subdrop')) {
            $('ul', $(this).parents('ul:first')).slideUp(350);
            $('a', $(this).parents('ul:first')).removeClass('subdrop');
            $(this).next('ul').slideDown(350);
            $(this).addClass('subdrop');
        } else if ($(this).hasClass('subdrop')) {
            $(this).removeClass('subdrop');
            $(this).next('ul').slideUp(350);
        }
    });

    // 3. Set Active State
    var $activeLink = $('#sidebar-menu ul li.submenu a.active');
    if ($activeLink.length > 0) {
        var $parentLi = $activeLink.parents('li:last');
        var $parentLink = $parentLi.children('a:first');
        
        $parentLink.addClass('active subdrop');
        $parentLink.next('ul').show();
    }
}

// 1. Initial Load
window.addEventListener('load', function() {
    // ... existing load logic ...
    
    // Override app.js default behavior with our unified logic
    setTimeout(initSidebar, 100);

    // Trigger same initialization for first load if needed or keep existing logic
    // Keeping existing dynamic script loader below
    
    var fileinputCSS = document.createElement('link');
    fileinputCSS.rel = 'stylesheet';
    fileinputCSS.href = '{{ asset("js/plugins/bootstrap-fileinput/fileinput.min.css") }}';
    document.head.appendChild(fileinputCSS);
    
    // Load local legacy scripts (libraries are now in app.js bundle)
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
            script.onload = function() {
                // Initialize datetimepicker if present and script loaded
                if (scriptInfo.id === 'datetimepicker-js') {
                    if ($(".datetimepicker").length > 0 && $.fn.datetimepicker) {
                        $(".datetimepicker").each(function () {
                            $(this).datetimepicker({
                                format: "YYYY-MM-DD H:i",
                                icons: {
                                    up: "fa fa-angle-up",
                                    down: "fa fa-angle-down",
                                    next: "fa fa-angle-right",
                                    previous: "fa fa-angle-left",
                                },
                            });
                        });
                    }
                }
            };
            document.body.appendChild(script);
        }
    });
});

// 2. Livewire Navigation
document.addEventListener('livewire:navigated', () => {
    console.log('🚀 Livewire SPA Navigation verified!');
    
    // 1. Run Unified Sidebar Init
    initSidebar();

    // 2. Datepickers
    if ($(".datetimepicker").length > 0 && $.fn.datetimepicker) {
        $(".datetimepicker").each(function () {
            $(this).datetimepicker({
                format: "YYYY-MM-DD H:i",
                icons: {
                    up: "fa fa-angle-up",
                    down: "fa fa-angle-down",
                    next: "fa fa-angle-right",
                    previous: "fa fa-angle-left",
                },
            });
        });
    }

    // 3. Select2 (if present)
    if ($('.select').length > 0 && $.fn.select2) {
        $('.select').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });
    }

    // 4. Tooltips
    if($('[data-toggle="tooltip"]').length > 0) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // 5. Popovers
    if($('[data-toggle="popover"]').length > 0) {
        $('[data-toggle="popover"]').popover();
    }
    
    // 6. Hide Loader
    $('#loader-wrapper').delay(100).fadeOut('slow');
    $('#loader-wrapper .loader-ellips').delay(100).fadeOut();
    
    // 7. Mobile Sidebar Close
    if ($(window).width() <= 991 && $('body').hasClass('slide-nav')) {
        $('.mobile_btn').trigger('click');
    }
});

// Remove global checks - simpler is better
// if (!window.sidebarInitialized) ... REMOVED
</script>

@stack('page-scripts')
@stack('page-script')
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
