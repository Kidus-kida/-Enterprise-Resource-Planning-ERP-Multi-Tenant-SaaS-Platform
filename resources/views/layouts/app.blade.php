@extends('layouts.blank')

@section('content')
    <!-- Header -->
    @include('partials.header')
    <!-- /Header -->
    <!-- Sidebar -->
    @hasSection('sidebar')
        @yield('sidebar')
    @else
        @include('partials.sidebar')
    @endif
    <!-- /Sidebar -->
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div id="loader-wrapper">
            <div id="loader">
                <div class="loader-ellips">
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                </div>
            </div>
        </div>
        <!-- Page Content -->
        @yield('page-content')
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->
    <!-- Delete Modal -->
    <div class="modal custom-modal fade" id="GeneralDeleteModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3 class="modal_title">Delete</h3>
                        <p class="modal_message">Are you sure want to delete?</p>
                    </div>
                    <form method="post">
                        @method('DELETE')
                        @csrf
                        <div class="modal-btn delete-action">
                            <input type="hidden" name="id">
                            <div class="row">
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-bs-dismiss="modal"
                                        class="btn btn-primary cancel-btn">{{ __('Cancel') }}</a>
                                </div>
                                <div class="col-6">
                                    <button type="submit"
                                        class="btn btn-primary continue-btn w-100">{{ __('Delete') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('page-scripts')
        <script>
            (function($) {
                function toggleHalfDay($modal) {
                    const checked = $modal.find('#halfDay').is(':checked');
                    if (checked) {
                        $modal.find('#half-day-time-section').show();
                        $modal.find('#leave-end-date-section').hide();
                        const start = $modal.find('#leave_start_date').val();
                        if (start) $modal.find('#leave_end_date').val(start);
                    } else {
                        $modal.find('#half-day-time-section').hide();
                        $modal.find('#leave-end-date-section').show();
                        $modal.find('#leave_end_date').val('');
                    }
                }

                $(document).on('click', '[data-ajax-modal="true"]', function(e) {
                    e.preventDefault();
                    const $link = $(this);
                    $.get($link.data('url'), function(html) {
                        const $modal = $('#ajaxModal');
                        $modal.find('.modal-dialog')
                            .removeClass('modal-sm modal-md modal-lg modal-xl')
                            .addClass('modal-' + ($link.data('size') || 'md'));
                        $modal.find('.modal-content').html(html);
                        $modal.modal('show');
                    });
                });

                $(document)
                    .on('change', '#halfDay', function() {
                        toggleHalfDay($(this).closest('.modal'));
                    })
                    .on('change', '#leave_start_date', function() {
                        const $m = $(this).closest('.modal');
                        if ($m.find('#halfDay').is(':checked')) {
                            $m.find('#leave_end_date').val($(this).val());
                        }
                    });

                $('#ajaxModal').on('shown.bs.modal', function() {
                    toggleHalfDay($(this));
                });
            })(jQuery);
        </script>
        <script>
            (function($) {
                /* ── helper ───────────────────────────────────────── */
                function toggleRejectReason($modal) {
                    const $select = $modal.find('#statusSelect');
                    const $row = $modal.find('#reject-reason-section');
                    const show = $select.val() === 'rejected';
                    $row.toggle(show);
                }
                /* ── delegated change on the <select> ─────────────── */
                $(document).on('change', '#statusSelect', function() {
                    toggleRejectReason($(this).closest('.modal'));
                });
                /* ── when ANY modal opens, sync its initial state ─── */
                $('#ajaxModal').on('shown.bs.modal', function() {
                    toggleRejectReason($(this));
                });
            })(jQuery);
        </script>
    @endpush
@endsection {{-- <- keep your original @endsection --}}
