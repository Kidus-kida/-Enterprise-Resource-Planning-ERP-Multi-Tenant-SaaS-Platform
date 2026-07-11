@extends('superadmin::settings.layout')

@push('styles')
<style>
/* ===== Dashboard Builder Styles ===== */
.widget-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: .75rem;
    min-height: 200px;
}
.widget-card {
    background: var(--bs-white, #fff);
    border: 1px solid #e3e6ef;
    border-radius: 8px;
    padding: 0;
    cursor: grab;
    transition: box-shadow .2s, transform .15s;
    position: relative;
    overflow: hidden;
}
.widget-card:active { cursor: grabbing; transform: scale(1.02); }
.widget-card:hover  { box-shadow: 0 4px 18px rgba(0,0,0,.12); }

.widget-card-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .75rem;
    border-bottom: 1px solid #f0f2f5;
    background: #f8fafc;
}
.widget-drag-handle { color: #adb5bd; cursor: grab; }
.widget-card-icon { font-size: 1rem; }
.widget-card-title { flex: 1; font-weight: 600; font-size: .8rem; color: #4e5e6a; }
.widget-card-actions { display: flex; gap: .25rem; }
.widget-card-actions button { border: none; padding: 3px 6px; border-radius: 4px; cursor: pointer; transition: background .15s; }
.widget-card-body { padding: .75rem; min-height: 60px; }
.widget-size-badge { font-size: .65rem; background: #e9ecef; border-radius: 4px; padding: 1px 5px; color: #6c757d; }

/* Width selector */
.width-selector { display: flex; gap: .25rem; flex-wrap: wrap; }
.width-btn { border: 1px solid #dee2e6; background: none; padding: 2px 8px; border-radius: 4px; font-size: .7rem; cursor: pointer; transition: all .15s; }
.width-btn.active, .width-btn:hover { background: var(--primary-color, #ff9b44); color: #fff; border-color: var(--primary-color, #ff9b44); }

/* Widget library */
.widget-library { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: .5rem; }
.widget-library-item {
    border: 1px dashed #dee2e6; border-radius: 8px; padding: .6rem .8rem;
    cursor: pointer; transition: all .15s;
    display: flex; align-items: center; gap: .5rem; font-size: .8rem;
}
.widget-library-item:hover { border-color: var(--primary-color, #ff9b44); background: rgba(255,155,68,.06); color: var(--primary-color, #ff9b44); }
.widget-library-item.already-added { opacity: .4; cursor: not-allowed; border-style: solid; }
</style>
@endpush

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-table-columns"></i> Dashboard Builder
        </h1>
        <p class="settings-section-subtitle">Choose which widgets appear on the dashboard and arrange their order and width.</p>
    </div>
</div>

{{-- Widget Library --}}
<div class="settings-card mb-3">
    <div class="settings-card-header">
        <i class="fa-solid fa-store me-2"></i>Widget Library — Click to Add
    </div>
    <div class="settings-card-body">
        <div class="widget-library" id="widgetLibrary">
            {{-- Populated by JS --}}
        </div>
    </div>
</div>

{{-- Active Dashboard --}}
<div class="settings-card mb-3">
    <div class="settings-card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-layout me-2"></i>Active Dashboard Layout</span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-danger" id="resetDashboardBtn">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset
            </button>
        </div>
    </div>
    <div class="settings-card-body">
        <div class="widget-grid" id="widgetGrid" style="grid-template-columns: repeat(12, 1fr);">
            <div class="text-center text-muted py-4 fst-italic" id="emptyDashboardNotice" style="grid-column: span 12;">
                <i class="fa-solid fa-circle-info me-2"></i>No widgets selected. Pick from the library above.
            </div>
        </div>
    </div>
</div>

{{-- Save --}}
<div class="settings-form-actions">
    <button class="btn btn-primary btn-save-settings" id="saveDashboardBtn">
        <i class="fa-solid fa-floppy-disk me-1"></i>
        <span class="btn-text">Save Dashboard Layout</span>
        <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
    </button>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const DASHBOARD_SAVE_URL = '{{ route("superadmin.settings.dashboard.save") }}';
const CSRF_TOKEN         = '{{ csrf_token() }}';
const SAVED_WIDGETS      = {!! json_encode(setting('dashboard.widgets') ? json_decode(setting('dashboard.widgets'), true) : null) !!};

/* ─── Widget definitions ─────────────────────────────────────────────────── */
const ALL_WIDGETS = [
    { id: 'revenue_summary',    label: 'Revenue Summary',       icon: 'fa-solid fa-chart-line',         color: '#ff9b44', width: 6,  description: 'Monthly & yearly revenue chart' },
    { id: 'total_employees',    label: 'Total Employees',        icon: 'fa-solid fa-users',              color: '#36b9cc', width: 3,  description: 'Headcount stat card' },
    { id: 'attendance_today',   label: 'Attendance Today',       icon: 'fa-solid fa-clock',              color: '#1cc88a', width: 3,  description: 'Present / Absent today' },
    { id: 'pending_leaves',     label: 'Pending Leaves',         icon: 'fa-solid fa-calendar-xmark',     color: '#f6c23e', width: 3,  description: 'Open leave approvals' },
    { id: 'payroll_summary',    label: 'Payroll Summary',        icon: 'fa-solid fa-money-bill-wave',    color: '#8b5cf6', width: 6,  description: 'Payroll cost breakdown' },
    { id: 'tasks_widget',       label: 'My Tasks',               icon: 'fa-solid fa-check-circle',       color: '#3b82f6', width: 4,  description: 'Task list for current user' },
    { id: 'announcements',      label: 'Announcements',          icon: 'fa-solid fa-bullhorn',           color: '#e74a3b', width: 6,  description: 'Company announcements feed' },
    { id: 'calendar_widget',    label: 'Mini Calendar',          icon: 'fa-solid fa-calendar-days',      color: '#0ea5e9', width: 4,  description: 'Calendar with upcoming events' },
    { id: 'recent_activities',  label: 'Recent Activities',      icon: 'fa-solid fa-list-check',         color: '#6c757d', width: 4,  description: 'Audit trail feed' },
    { id: 'top_employees',      label: 'Top Employees',          icon: 'fa-solid fa-trophy',             color: '#f59e0b', width: 4,  description: 'Performance leaderboard' },
    { id: 'leave_summary',      label: 'Leave Summary',          icon: 'fa-solid fa-chart-pie',          color: '#10b981', width: 3,  description: 'Leave usage pie chart' },
    { id: 'quick_actions',      label: 'Quick Actions',          icon: 'fa-solid fa-bolt',               color: '#6366f1', width: 3,  description: 'Shortcut buttons' },
    { id: 'projects_overview',  label: 'Projects Overview',      icon: 'fa-solid fa-diagram-project',    color: '#14b8a6', width: 6,  description: 'Active project progress bars' },
    { id: 'sales_chart',        label: 'Sales Chart',            icon: 'fa-solid fa-chart-bar',          color: '#f43f5e', width: 6,  description: 'Sales pipeline bar chart' },
    { id: 'weather_widget',     label: 'Weather',                icon: 'fa-solid fa-cloud-sun',          color: '#38bdf8', width: 3,  description: 'Local weather card' },
    { id: 'open_tickets',       label: 'Open Tickets',           icon: 'fa-solid fa-ticket',             color: '#c084fc', width: 3,  description: 'Unresolved support tickets' },
];

const DEFAULT_LAYOUT = ['revenue_summary','total_employees','attendance_today','pending_leaves','payroll_summary','tasks_widget','recent_activities','quick_actions'];

/* ─── State ──────────────────────────────────────────────────────────────── */
let activeWidgets = [];

function uid(base) { return base; } // use widget id directly

/* ─── Init from saved or defaults ───────────────────────────────────────── */
if (SAVED_WIDGETS && SAVED_WIDGETS.length) {
    activeWidgets = SAVED_WIDGETS.map(saved => {
        const def = ALL_WIDGETS.find(w => w.id === saved.id);
        return def ? { ...def, ...saved } : null;
    }).filter(Boolean);
} else {
    activeWidgets = DEFAULT_LAYOUT.map(id => ALL_WIDGETS.find(w => w.id === id)).filter(Boolean)
        .map(w => ({ ...w }));
}

/* ─── Render Library ─────────────────────────────────────────────────────── */
function renderLibrary() {
    const lib = document.getElementById('widgetLibrary');
    lib.innerHTML = '';
    ALL_WIDGETS.forEach(w => {
        const added = activeWidgets.some(a => a.id === w.id);
        const div = document.createElement('div');
        div.className = 'widget-library-item' + (added ? ' already-added' : '');
        div.dataset.id = w.id;
        div.innerHTML = `<i class="${w.icon}" style="color:${w.color}"></i><span>${w.label}</span>`;
        if (!added) {
            div.addEventListener('click', () => {
                activeWidgets.push({ ...w });
                renderAll();
            });
        }
        lib.appendChild(div);
    });
}

/* ─── Render Grid  ───────────────────────────────────────────────────────── */
function renderGrid() {
    const grid   = document.getElementById('widgetGrid');
    const notice = document.getElementById('emptyDashboardNotice');

    notice.style.display = activeWidgets.length ? 'none' : '';
    // Clear all widgets (keep notice)
    Array.from(grid.children).forEach(el => { if (el.id !== 'emptyDashboardNotice') el.remove(); });

    activeWidgets.forEach(w => {
        const div = document.createElement('div');
        div.className = 'widget-card';
        div.dataset.id = w.id;
        div.style.gridColumn = `span ${w.width || 6}`;
        div.innerHTML = `
        <div class="widget-card-header">
            <span class="widget-drag-handle"><i class="fa-solid fa-grip-vertical fa-xs"></i></span>
            <i class="${w.icon} widget-card-icon" style="color:${w.color}"></i>
            <span class="widget-card-title">${w.label}</span>
            <span class="widget-size-badge">${w.width || 6}/12</span>
            <div class="widget-card-actions">
                <button class="btn-remove-widget" title="Remove" style="color:#dc3545;background:none">
                    <i class="fa-solid fa-times fa-xs"></i>
                </button>
            </div>
        </div>
        <div class="widget-card-body">
            <p class="text-muted small mb-2">${w.description || ''}</p>
            <div class="width-selector">
                ${[3,4,6,8,9,12].map(n => `<button class="width-btn${w.width === n ? ' active' : ''}" data-width="${n}" data-id="${w.id}">${n} col</button>`).join('')}
            </div>
        </div>
        `;

        div.querySelector('.btn-remove-widget').addEventListener('click', () => {
            activeWidgets = activeWidgets.filter(a => a.id !== w.id);
            renderAll();
        });

        div.querySelectorAll('.width-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const wid = btn.dataset.id;
                const n   = parseInt(btn.dataset.width);
                const item = activeWidgets.find(a => a.id === wid);
                if (item) { item.width = n; renderAll(); }
            });
        });

        grid.appendChild(div);
    });

    // Attach Sortable
    new Sortable(grid, {
        animation: 150,
        handle: '.widget-drag-handle',
        filter: '#emptyDashboardNotice',
        onEnd() {
            const newOrder = Array.from(grid.querySelectorAll('.widget-card')).map(el => el.dataset.id);
            activeWidgets = newOrder.map(id => activeWidgets.find(w => w.id === id)).filter(Boolean);
        }
    });
}

function renderAll() {
    renderLibrary();
    renderGrid();
}

/* ─── Save ───────────────────────────────────────────────────────────────── */
document.getElementById('saveDashboardBtn').addEventListener('click', async () => {
    const btn     = document.getElementById('saveDashboardBtn');
    const btnText = btn.querySelector('.btn-text');
    const loading = btn.querySelector('.btn-loading');
    btnText.classList.add('d-none');
    loading.classList.remove('d-none');

    try {
        const res = await fetch(DASHBOARD_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ widgets: activeWidgets })
        });
        const data = await res.json();
        const toast = document.getElementById('settingsToast');
        if (toast) {
            const toastMsg = toast.querySelector('.toast-message');
            const toastIcon = toast.querySelector('.toast-icon');
            if (toastMsg) toastMsg.textContent = data.message || (data.success ? 'Dashboard layout saved!' : 'Save failed.');
            if (toastIcon) toastIcon.className = 'fa-solid ' + (data.success !== false ? 'fa-circle-check toast-icon text-success' : 'fa-circle-xmark toast-icon text-danger');
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }
    } catch(e) {
        alert('Network error. Please try again.');
    } finally {
        btnText.classList.remove('d-none');
        loading.classList.add('d-none');
    }
});

/* ─── Reset ──────────────────────────────────────────────────────────────── */
document.getElementById('resetDashboardBtn').addEventListener('click', () => {
    if (!confirm('Reset to the default dashboard widget layout?')) return;
    activeWidgets = DEFAULT_LAYOUT.map(id => ALL_WIDGETS.find(w => w.id === id)).filter(Boolean).map(w => ({ ...w }));
    renderAll();
});

/* ─── Boot ───────────────────────────────────────────────────────────────── */
renderAll();
</script>
@endpush
