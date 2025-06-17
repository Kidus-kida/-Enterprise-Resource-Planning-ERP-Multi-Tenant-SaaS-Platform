@extends('layouts.app', ['pageTitle' => __('Assets')])

@section('page-content')
    <div class="content container-fluid">

        <div class="row g-4">
            {{-- ==================== LEFT COLUMN ==================== --}}
            <div class="col-lg-6">
                {{-- ---------- Leave‑request info card ---------- --}}
                <div class="card h-95 shadow-sm">
                    <h3 class="card-header">{{ __('Leave‑request Detail Info') }}</h3>
                    <div class="list-group list-group-flush">
                        @php($emp = $leaverequest->employee)
                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __('Applicant Name') }}</span>
                            <span>{{ $emp->firstname }} {{ $emp->middlename }}</span>
                        </div>

                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __('Leave Type') }}</span>
                            <span>{{ $leaverequest->leaveType->type_name }}</span>
                        </div>

                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __('Leave Start Date') }}</span>
                            <span>{{ format_date($leaverequest->leave_start_date) }}</span>
                        </div>

                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __('Leave End Date') }}</span>
                            <span>{{ format_date($leaverequest->leave_end_date) }}</span>
                        </div>

                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __('Request Status') }}</span>
                            <span class="text-capitalize">{{ $leaverequest->status }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>{{ __('Attended By') }}</span>
                            <span class="text-capitalize">{{ $leaverequest->admin->firstname ?? 'N/A' }}
                                {{ $leaverequest->admin->middlename ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ---------- Purchase & Warranty card ---------- --}}
                <div class="card mt-4 shadow-sm">
                    <h3 class="card-header">{{ __('Detail Request/Reject Reason') }}</h3>
                    <div class="list-group list-group-flush">

                        <div class="list-group-item">
                            <strong>{{ __('Request Reason') }}</strong>
                            <p class="mt-2 mb-0 text-wrap">{{ $leaverequest->request_reason }}</p>
                        </div>

                        <div class="list-group-item">
                            <strong>{{ __('Reject Reason') }}</strong>
                            <p class="mt-2 mb-0 text-wrap">{{ $leaverequest->reject_reason }}</p>
                        </div>

                    </div>
                </div>

            </div>

            {{-- ==================== RIGHT COLUMN ==================== --}}
            <div class="col-lg-6">
                {{-- ---------- History card ---------- --}}
                <div class="h-95 shadow-sm">
                    <h3 class="card-header text-capitalize">{{ __('Leave‑request Documents Attachements') }}</h3>
                    <div class="list-group list-group-flush">

                        @if (is_array($leaverequest->attachements))
                            @foreach ($leaverequest->attachements as $file)
                                <div class="col-4 text-center">
                                    <a href="{{ asset('storage/leave_attachments/' . $file) }}" target="_blank">
                                        <img src="{{ asset('storage/leave_attachments/' . $file) }}" alt="Attachment"
                                            class="img-fluid rounded border" style="min-height: 200px;min-width: 600px;">
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-muted">No attachments available</div>
                        @endif


                    </div>
                </div>

            </div>
        </div> {{-- /.row --}}
    </div>
@endsection


@push('page-scripts')
@endpush
