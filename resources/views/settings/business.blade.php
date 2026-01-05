@extends('layouts.app')

@push('page-styles')
    <style>
        .pos-tab-container {
            border-top: 1px solid #f4f4f4;
            background-color: #fff;
        }

        .pos-tab-menu {
            padding-right: 0;
            padding-left: 0;
            border-right: 1px solid #f4f4f4;
        }

        .pos-tab-menu .list-group {
            margin-bottom: 0;
            border-radius: 0;
        }

        .pos-tab-menu .list-group-item {
            border: none;
            border-bottom: 1px solid #f4f4f4;
            margin-bottom: 0;
            border-radius: 0;
            cursor: pointer;
            padding: 12px 15px;
            color: #333;
        }

        .pos-tab-menu .list-group-item.active {
            background-color: #3c8dbc;
            border-color: #3c8dbc;
            color: #fff;
            font-weight: bold;
        }

        .pos-tab-menu .list-group-item:hover {
            background-color: #f4f4f4;
            color: #333;
        }

        .pos-tab-menu .list-group-item.active:hover {
            background-color: #367fa9;
            color: #fff;
        }

        .pos-tab-content {
            padding: 20px;
            background-color: #fff;
        }

        /* Essential for tab toggling */
        .pos-tab {
            display: none;
        }

        .pos-tab.active {
            display: block;
        }
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Business Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Business Settings</h4>
                    </div>
                    <div class="card-body p-0">
                        <form action="{{ route('settings.business.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-0">
                                <div class="col-lg-2 col-md-3 pos-tab-menu border-end">
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item active text-start">Business</a>
                                        <a href="#" class="list-group-item text-start">Tax</a>
                                        <a href="#" class="list-group-item text-start">Product</a>
                                        <a href="#" class="list-group-item text-start">Contact</a>
                                        <a href="#" class="list-group-item text-start">Sale</a>
                                        <a href="#" class="list-group-item text-start">POS</a>
                                        <a href="#" class="list-group-item text-start">Purchases</a>
                                        <a href="#" class="list-group-item text-start">Payment</a>
                                        <a href="#" class="list-group-item text-start">Dashboard</a>
                                        <a href="#" class="list-group-item text-start">System</a>
                                        <a href="#" class="list-group-item text-start">Prefixes</a>
                                        <a href="#" class="list-group-item text-start">Email Settings</a>
                                        <a href="#" class="list-group-item text-start">Reward Point Settings</a>
                                        <a href="#" class="list-group-item text-start">Access Modules</a>
                                        <a href="#" class="list-group-item text-start">Custom Labels</a>
                                        <a href="#" class="list-group-item text-start">Stores</a>
                                        {{-- <a href="#" class="list-group-item text-start">Restaurant</a> --}}
                                        {{-- <a href="#" class="list-group-item text-start">Booking</a> --}}
                                        <a href="#" class="list-group-item text-start">Upload Images</a>
                                        <a href="#" class="list-group-item text-start">Customer & Supplier</a>
                                    </div>
                                </div>
                                <div class="col-lg-10 col-md-9 pos-tab-content">
                                    <!-- Add active class to the first tab by default in HTML as well for safety -->
                                    <div class="pos-tab active">
                                        @include('settings.partials.settings_business')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_tax')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_product')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_contact')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_sales')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_pos')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_purchase')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_payment')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_dashboard')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_system')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_prefixes')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_email')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_reward_point')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_modules')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_custom_labels')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_stores')
                                    </div>
                                    {{-- <div class="pos-tab">
                                        @include('settings.partials.settings_restaurant')
                                    </div> --}}
                                    {{-- <div class="pos-tab">
                                        @include('settings.partials.settings_booking')
                                    </div> --}}
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_upload_images')
                                    </div>
                                    <div class="pos-tab">
                                        @include('settings.partials.settings_customer_supplier')
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4 text-center pb-3">
                                <button type="submit" class="btn btn-primary btn-lg">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        // Use window.load to ensure all resources (including jQuery from Vite) are loaded
        window.addEventListener('load', function() {
            // Function to handle tab switching
            function switchTab(index) {
                // Remove active class from all menu items and add to current
                $('.pos-tab-menu .list-group-item').removeClass('active');
                $('.pos-tab-menu .list-group-item').eq(index).addClass('active');

                // Hide all tabs and show current
                $('.pos-tab').hide().removeClass('active');
                $('.pos-tab').eq(index).fadeIn(300).addClass('active');
            }

            // Click Handler
            $('.pos-tab-menu .list-group-item').click(function(e) {
                e.preventDefault();
                var index = $(this).index();
                switchTab(index);
            });

            // Initialize: Ensure only active tab is shown (fixes the issue if CSS was lagging)
            // If HTML has 'active' class on one tab, hide others.
            $('.pos-tab').not('.active').hide();
            // If no tab is active, activate first
            if ($('.pos-tab.active').length === 0) {
                switchTab(0);
            }

            // Hash navigation support (optional but useful)
            // If needed, check window.location.hash and switchTab accordingly

            // Initialize Select2
            if ($('.select').length > 0) {
                try {
                    $('.select').select2({
                        minimumResultsForSearch: -1,
                        width: '100%'
                    });
                } catch (e) {
                    console.warn('Select2 initialization failed', e);
                }
            }

            // Toggle business categories
            $(document).on('change', '#show_for_customers', function() {
                if ($(this).is(':checked')) {
                    $('#business_categories_div').removeClass('d-none');
                } else {
                    $('#business_categories_div').addClass('d-none');
                }
            });

            @if (session('status'))
                // Ensure toastr or alert is used
                alert("{{ session('status')['msg'] }}");
            @endif
        });
    </script>
@endpush
