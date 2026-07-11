@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-file-lines"></i> Settings Audit Logs</h1>
        <p class="settings-section-subtitle">View and audit all historical modifications made to system configuration settings.</p>
    </div>
</div>

{{-- Filters --}}
<div class="settings-card mb-4">
    <div class="settings-card-body">
        <form method="GET" action="{{ route('superadmin.settings.audit-logs') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="setting-label mb-1">Filter by Setting Key</label>
                <input type="text" name="key" class="form-control form-control-sm"
                       value="{{ request('key') }}" placeholder="e.g. general.system_name">
            </div>
            <div class="col-md-4">
                <label class="setting-label mb-1">Filter by User ID</label>
                <input type="number" name="user_id" class="form-control form-control-sm"
                       value="{{ request('user_id') }}" placeholder="e.g. 1">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('superadmin.settings.audit-logs') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Logs Table --}}
<div class="settings-card">
    <div class="settings-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 170px;">Date &amp; Time</th>
                        <th>User</th>
                        <th>Setting Key</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th style="width: 120px;">IP Address</th>
                        <th style="width: 150px;">Metadata</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ $log->changed_at ? $log->changed_at->format('Y-m-d H:i:s') : 'N/A' }}
                        </td>
                        <td>
                            @if($log->user)
                                <span>{{ $log->user->name }}</span>
                                <small class="text-muted d-block" style="font-size: 10px;">ID: {{ $log->user_id }}</small>
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td class="fw-semibold text-primary">
                            {{ $log->key }}
                        </td>
                        <td>
                            @if(in_array($log->key, ['email.smtp_password', 'integration.google_client_secret', 'integration.paypal_client_secret', 'integration.telebirr_app_key', 'integration.slack_webhook', 'integration.telegram_bot_token', 'integration.stripe_secret', 'storage.s3_secret', 'license.key']))
                                <span class="text-muted">••••••••</span>
                            @else
                                <code class="text-wrap break-all">{{ Str::limit($log->old_value, 50) ?: '[empty]' }}</code>
                            @endif
                        </td>
                        <td>
                            @if(in_array($log->key, ['email.smtp_password', 'integration.google_client_secret', 'integration.paypal_client_secret', 'integration.telebirr_app_key', 'integration.slack_webhook', 'integration.telegram_bot_token', 'integration.stripe_secret', 'storage.s3_secret', 'license.key']))
                                <span class="text-muted">••••••••</span>
                            @else
                                <code class="text-wrap break-all">{{ Str::limit($log->new_value, 50) ?: '[empty]' }}</code>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ $log->ip_address }}
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1" style="font-size: 11px;">
                                <span><i class="fa-solid fa-desktop me-1"></i> {{ $log->device_type ?? 'Unknown' }} ({{ $log->browser_type ?? 'Unknown' }})</span>
                                @if($log->request_id)
                                    <span class="text-muted" title="HTTP Request ID"><i class="fa-solid fa-hashtag me-1"></i> {{ Str::limit($log->request_id, 8, '') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
                            <p class="mb-0">No setting audit logs found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-top py-2">
        {{ $logs->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
