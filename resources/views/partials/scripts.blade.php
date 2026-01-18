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
// Function to remove any direct listeners (which cause double-toggles)
function cleanupDirectListeners() {
    $('#sidebar-menu a').off('click');
}

// Function to manually expand active menu (without triggering clicks)
function expandActiveMenu() {
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
    
    // Cleanup direct listeners that app.js attached
    setTimeout(function() {
        cleanupDirectListeners();
        expandActiveMenu();
    }, 100);

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
    // 1. Sidebar Cleanup & Restore
    // We strictly remove direct listeners to force usage of our delegated listener below
    cleanupDirectListeners();
    expandActiveMenu();

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

// SINGLETON DELEGATED EVENT LISTENER
// This runs once and handles clicks forever (even after DOM swap)
if (!window.sidebarDelegated) {
    $(document).on('click', '#sidebar-menu a', function (e) {
        // Prevent default if it's a submenu toggle
        if ($(this).parent().hasClass('submenu')) {
            e.preventDefault();
        }
        
        if (!$(this).hasClass('subdrop')) {
            // Opening: Hide others, show this
            $('ul', $(this).parents('ul:first')).slideUp(350);
            $('a', $(this).parents('ul:first')).removeClass('subdrop');
            $(this).next('ul').slideDown(350);
            $(this).addClass('subdrop');
        } else if ($(this).hasClass('subdrop')) {
            // Closing: Just hide this
            $(this).removeClass('subdrop');
            $(this).next('ul').slideUp(350);
        }
    });
    window.sidebarDelegated = true;
}

// Remove global checks - simpler is better
// if (!window.sidebarInitialized) ... REMOVED
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
