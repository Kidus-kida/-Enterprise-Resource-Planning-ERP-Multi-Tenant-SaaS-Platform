<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@vite([
    'resources/js/app.js',
    'resources/assets/js/bootstrap.bundle.min.js',
    'resources/assets/js/jquery.slimscroll.min.js',
    'resources/assets/plugins/jquery-repeater/jquery.repeater.min.js',
    'resources/assets/js/app.js',
])
<!-- Vendor JS -->

@livewireScriptConfig 
@yield('vendor-scripts')
@stack('page-scripts')
<script defer src="{{ asset('js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
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
    
    // Global AJAX Modal Handler
    $(document).on('click', '[data-ajax-modal="true"]', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var title = $(this).data('title') || 'Modal';
        var size = $(this).data('size') || 'md';
        
        if (!url) {
            console.error('No URL provided for modal');
            return;
        }
        
        // Create modal if it doesn't exist
        if ($('#ajaxModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-${size}" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body"></div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        // Update modal size
        $('#ajaxModal .modal-dialog').attr('class', `modal-dialog modal-${size}`);
        
        // Set title and load content
        $('#ajaxModal .modal-title').text(title);
        $('#ajaxModal .modal-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        
        // Show modal
        $('#ajaxModal').modal('show');
        
        // Load content via AJAX
        $.get(url)
            .done(function(data) {
                $('#ajaxModal .modal-body').html(data);
            })
            .fail(function() {
                $('#ajaxModal .modal-body').html('<div class="alert alert-danger">Error loading content</div>');
            });
    });
    
    // Global Delete Handler
    $(document).on('click', '.deleteBtn', function(e) {
        e.preventDefault();
        var route = $(this).data('route');
        var title = $(this).data('title') || 'Delete';
        var question = $(this).data('question') || 'Are you sure you want to delete this item?';
        
        if (confirm(question)) {
            // Create a form and submit it
            var form = $('<form>', {
                'method': 'POST',
                'action': route
            });
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_method',
                'value': 'DELETE'
            }));
            
            $('body').append(form);
            form.submit();
        }
    });
</script>
