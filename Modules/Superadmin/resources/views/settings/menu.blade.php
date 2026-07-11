@extends('superadmin::settings.layout')

@push('styles')
<style>
/* ===== Menu Builder Styles ===== */
.menu-builder-layout {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
}
.menu-builder-panel {
    flex: 1;
    min-width: 0;
}
.menu-builder-preview {
    width: 280px;
    flex-shrink: 0;
}
.menu-item-list {
    list-style: none;
    margin: 0;
    padding: 0;
    min-height: 60px;
}
.menu-item {
    background: var(--bs-white, #fff);
    border: 1px solid #e3e6ef;
    border-radius: 6px;
    margin-bottom: 6px;
    padding: 0;
    transition: box-shadow .15s;
}
.menu-item:hover { box-shadow: 0 2px 10px rgba(0,0,0,.1); }
.menu-item-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem .85rem;
    cursor: grab;
}
.menu-item-header:active { cursor: grabbing; }
.menu-item-drag { color: #adb5bd; font-size: 1rem; }
.menu-item-icon { color: #6c757d; width: 18px; text-align: center; }
.menu-item-label { flex: 1; font-weight: 500; font-size: .875rem; }
.menu-item-badges { display: flex; gap: .4rem; }
.menu-item-actions { display: flex; gap: .25rem; }
.menu-item-actions button { background: none; border: none; padding: 3px 6px; color: #6c757d; border-radius: 4px; cursor: pointer; transition: background .15s, color .15s; }
.menu-item-actions button:hover { background: #f0f2f5; color: #495057; }
.menu-item-actions button.delete-item:hover { color: #dc3545; background: #fdf1f2; }
.menu-item-children {
    padding: 0 0.5rem 0.5rem 2rem;
    display: none;
}
.menu-item-children.open { display: block; }
.menu-item-children .menu-item {
    border-color: #d3dae3;
    background: #f8fafc;
}
.menu-item-title .menu-item-header { background: #fffbe6; border-radius: 6px; cursor: pointer; }
.disabled-item .menu-item-header { opacity: .55; }
.hidden-item .menu-item-header { opacity: .55; background: #fafafa; text-decoration: line-through; }

/* Add Item Form */
.add-item-card { background: #f8fafc; border: 2px dashed #dee2e6; border-radius: 8px; padding: 1rem; }
.add-item-card label { font-size: .8rem; font-weight: 600; color: #495057; margin-bottom: .2rem; }
.form-icon-picker { position: relative; }
.icon-preview { width: 34px; height: 34px; line-height: 34px; text-align: center; border: 1px solid #dee2e6; border-radius: 5px; color: #6c757d; }

/* Sidebar preview */
.sidebar-preview { background: var(--sidebar-bg, #2c3e50); border-radius: 8px; padding: 1rem 0; }
.sidebar-preview-item {
    display: flex; align-items: center; gap: .6rem;
    padding: .45rem 1rem; font-size: .82rem;
    color: rgba(255,255,255,.75); border-left: 3px solid transparent;
    cursor: default;
}
.sidebar-preview-item.active { color: #fff; border-left-color: var(--primary-color, #ff9b44); background: rgba(255,255,255,.08); }
.sidebar-preview-title { padding: .6rem 1rem .2rem; font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.4); }
.sidebar-preview-arrow { margin-left: auto; font-size: .7rem; color: rgba(255,255,255,.35); }
.sidebar-preview-badge { font-size: .7rem; padding: 1px 6px; border-radius: 10px; background: var(--primary-color, #ff9b44); color: #fff; }
.sidebar-preview-children { padding-left: 1.2rem; }
.sidebar-preview-children .sidebar-preview-item { font-size: .78rem; }
</style>
@endpush

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-bars"></i> Menu Builder
        </h1>
        <p class="settings-section-subtitle">Create and reorder sidebar menu items. Changes affect all users. Drag to reorder, expand to add sub-items.</p>
    </div>
</div>

<div class="menu-builder-layout" id="menuBuilderLayout">

    {{-- Left: Item list + Add form --}}
    <div class="menu-builder-panel">

        {{-- Current Menu Items --}}
        <div class="settings-card mb-3">
            <div class="settings-card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-list-ul me-2"></i>Menu Items</span>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="collapseAllBtn">
                        <i class="fa-solid fa-compress-alt me-1"></i> Collapse All
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="resetMenuBtn">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset to Default
                    </button>
                </div>
            </div>
            <div class="settings-card-body">
                <ul class="menu-item-list" id="menuItemList">
                    {{-- Rendered via JS from menu structure --}}
                    <li class="text-center text-muted py-3 fst-italic" id="emptyMenuNotice">
                        <i class="fa-solid fa-circle-info me-2"></i>No custom menu configured. Add items below or import defaults.
                    </li>
                </ul>
            </div>
        </div>

        {{-- Add Item Form --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fa-solid fa-plus-circle me-2"></i>Add Menu Item
            </div>
            <div class="settings-card-body">
                <div class="add-item-card">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Label *</label>
                            <input type="text" class="form-control form-control-sm" id="newItemLabel" placeholder="e.g. Dashboard">
                        </div>
                        <div class="col-md-4">
                            <label>Route / URL</label>
                            <input type="text" class="form-control form-control-sm" id="newItemRoute" placeholder="e.g. dashboard">
                        </div>
                        <div class="col-md-4">
                            <label>Icon Class</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text icon-preview" id="iconPreviewEl"><i class="la la-folder"></i></span>
                                <input type="text" class="form-control" id="newItemIcon" placeholder="la la-home" value="la la-folder">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>Type</label>
                            <select class="form-select form-select-sm" id="newItemType">
                                <option value="link">Link</option>
                                <option value="title">Section Title</option>
                                <option value="external">External Link</option>
                                <option value="submenu">Submenu (parent)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Parent (sub-item of)</label>
                            <select class="form-select form-select-sm" id="newItemParent">
                                <option value="">— Top Level —</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Badge Count</label>
                            <input type="text" class="form-control form-control-sm" id="newItemBadge" placeholder="e.g. 5">
                        </div>
                        <div class="col-md-3">
                            <label>Permission (optional)</label>
                            <input type="text" class="form-control form-control-sm" id="newItemPermission" placeholder="view-employees">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary btn-sm" id="addMenuItemBtn">
                            <i class="fa-solid fa-plus me-1"></i> Add Item
                        </button>
                        <div class="form-check form-switch d-flex align-items-center ms-auto">
                            <input class="form-check-input me-2" type="checkbox" id="newItemVisible" checked>
                            <label class="form-check-label small" for="newItemVisible">Visible</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save bar --}}
        <div class="settings-form-actions mt-3">
            <button class="btn btn-primary btn-save-settings" id="saveMenuBtn">
                <i class="fa-solid fa-floppy-disk me-1"></i>
                <span class="btn-text">Save Menu Structure</span>
                <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
            </button>
            <button class="btn btn-outline-secondary ms-2" id="importDefaultMenuBtn" type="button">
                <i class="fa-solid fa-download me-1"></i> Use App Default
            </button>
        </div>
    </div>

    {{-- Right: Live Preview --}}
    <div class="menu-builder-preview d-none d-xl-block">
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fa-solid fa-eye me-2"></i>Live Preview
            </div>
            <div class="settings-card-body p-0">
                <div class="sidebar-preview" id="sidebarPreview">
                    <div class="px-3 py-2 text-center text-white-50 small fst-italic">
                        Add items to see the preview
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Item Edit Modal --}}
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Menu Item</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editItemId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label form-label-sm">Label</label>
                        <input type="text" class="form-control form-control-sm" id="editItemLabel">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm">Route / URL</label>
                        <input type="text" class="form-control form-control-sm" id="editItemRoute">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm">Icon</label>
                        <input type="text" class="form-control form-control-sm" id="editItemIcon">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm">Badge Count</label>
                        <input type="text" class="form-control form-control-sm" id="editItemBadge" placeholder="optional number / text">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm">Color</label>
                        <input type="color" class="form-control form-control-sm form-control-color" id="editItemColor" value="#ffffff">
                    </div>
                    <div class="col-12">
                        <label class="form-label form-label-sm">Required Permission (comma-separated)</label>
                        <input type="text" class="form-control form-control-sm" id="editItemPermissions" placeholder="view-employees,view-departments">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editItemVisible">
                            <label class="form-check-label small" for="editItemVisible">Visible</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editItemDisabled">
                            <label class="form-check-label small" for="editItemDisabled">Disabled</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editItemExternal">
                            <label class="form-check-label small" for="editItemExternal">External link</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveEditItemBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const MENU_SAVE_URL   = '{{ route("superadmin.settings.menu.save") }}';
const CSRF_TOKEN      = '{{ csrf_token() }}';
const INITIAL_MENU    = {!! json_encode(setting('menu.structure') ? json_decode(setting('menu.structure'), true) : []) !!};

// ─── State ────────────────────────────────────────────────────────────────────
let menuStructure = Array.isArray(INITIAL_MENU) ? INITIAL_MENU : [];
let editingId     = null;

function uid() {
    return 'item_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
}

// ─── Render ───────────────────────────────────────────────────────────────────
function renderMenu() {
    const list    = document.getElementById('menuItemList');
    const notice  = document.getElementById('emptyMenuNotice');
    const preview = document.getElementById('sidebarPreview');
    const parent  = document.getElementById('newItemParent');

    notice.style.display = menuStructure.length ? 'none' : '';

    // Populate the parent dropdown
    parent.innerHTML = '<option value="">— Top Level —</option>';
    menuStructure.filter(i => i.children !== undefined || i.is_title !== true).forEach(i => {
        if (i.is_title) return;
        const opt = document.createElement('option');
        opt.value = i.id;
        opt.textContent = i.label;
        parent.appendChild(opt);
    });

    // Draw sortable item list
    list.innerHTML = '';
    menuStructure.forEach((item, idx) => {
        list.appendChild(buildItemEl(item, idx));
    });

    // Re-attach Sortable
    attachSortable(list, null);
    list.querySelectorAll('.menu-item-children').forEach(ul => {
        const parentId = ul.dataset.parentId;
        attachSortable(ul, parentId);
    });

    // Draw Preview
    renderPreview(preview, menuStructure);
}

function buildItemEl(item, idx, isChild = false) {
    const li = document.createElement('li');
    li.className = 'menu-item' +
        (item.is_title    ? ' menu-item-title'   : '') +
        (item.is_disabled ? ' disabled-item'      : '') +
        (!item.is_visible && item.is_visible !== undefined ? ' hidden-item' : '');
    li.dataset.id = item.id;

    const hasChildren = item.children && item.children.length > 0;

    li.innerHTML = `
    <div class="menu-item-header">
        <i class="fa-solid fa-grip-vertical menu-item-drag"></i>
        <i class="${item.icon || 'la la-folder'} menu-item-icon"></i>
        <span class="menu-item-label">${item.label}</span>
        <span class="menu-item-badges">
            ${item.is_title ? '<span class="badge bg-warning text-dark" style="font-size:.65rem">TITLE</span>' : ''}
            ${item.is_external ? '<span class="badge bg-info" style="font-size:.65rem">EXT</span>' : ''}
            ${item.is_disabled ? '<span class="badge bg-secondary" style="font-size:.65rem">OFF</span>' : ''}
            ${item.badge_count ? '<span class="badge bg-primary" style="font-size:.65rem">'+item.badge_count+'</span>' : ''}
        </span>
        <span class="menu-item-actions">
            ${!item.is_title && !isChild ? `<button title="Toggle sub-items" class="toggle-children-btn"><i class="fa-solid fa-chevron-down fa-xs"></i></button>` : ''}
            <button title="Edit" class="edit-item-btn"><i class="fa-solid fa-pen fa-xs"></i></button>
            <button title="Remove" class="delete-item"><i class="fa-solid fa-trash fa-xs"></i></button>
        </span>
    </div>
    ${!isChild && !item.is_title ? `<ul class="menu-item-children" data-parent-id="${item.id}">
        ${(item.children || []).map((c, ci) => buildItemEl(c, ci, true).outerHTML).join('')}
    </ul>` : ''}
    `;

    // Toggle children
    const toggleBtn = li.querySelector('.toggle-children-btn');
    const childList = li.querySelector('.menu-item-children');
    if (toggleBtn && childList) {
        if (hasChildren) childList.classList.add('open');
        toggleBtn.addEventListener('click', () => {
            childList.classList.toggle('open');
            toggleBtn.querySelector('i').classList.toggle('fa-chevron-down');
            toggleBtn.querySelector('i').classList.toggle('fa-chevron-up');
        });
    }

    // Edit
    li.querySelector('.edit-item-btn')?.addEventListener('click', () => openEditModal(item.id));

    // Delete
    li.querySelector('.delete-item').addEventListener('click', () => {
        if (!confirm(`Remove "${item.label}"?`)) return;
        deleteItem(item.id);
    });

    return li;
}

function renderPreview(container, items) {
    container.innerHTML = '';
    if (!items.length) {
        container.innerHTML = '<div class="px-3 py-2 text-center text-white-50 small fst-italic">Add items to see the preview</div>';
        return;
    }
    items.forEach(item => {
        if (!item.is_visible && item.is_visible !== undefined) return;
        if (item.is_title) {
            const div = document.createElement('div');
            div.className = 'sidebar-preview-title';
            div.textContent = item.label;
            container.appendChild(div);
            return;
        }
        const div = document.createElement('div');
        div.className = 'sidebar-preview-item';
        div.innerHTML = `<i class="${item.icon || 'la la-folder'}"></i> <span>${item.label}</span>`;
        if (item.badge_count) {
            div.innerHTML += ` <span class="sidebar-preview-badge">${item.badge_count}</span>`;
        }
        if (item.children && item.children.length) {
            div.innerHTML += `<span class="sidebar-preview-arrow"><i class="fa-solid fa-chevron-right fa-xs"></i></span>`;
        }
        container.appendChild(div);
        if (item.children && item.children.length) {
            const sub = document.createElement('div');
            sub.className = 'sidebar-preview-children';
            item.children.filter(c => c.is_visible !== false).forEach(c => {
                const cd = document.createElement('div');
                cd.className = 'sidebar-preview-item';
                cd.innerHTML = `<i class="${c.icon || 'la la-circle'}"></i> <span>${c.label}</span>`;
                sub.appendChild(cd);
            });
            container.appendChild(sub);
        }
    });
}

// ─── SortableJS ────────────────────────────────────────────────────────────────
function attachSortable(el, parentId) {
    new Sortable(el, {
        animation: 150,
        handle: '.menu-item-drag',
        group: parentId ? 'children-' + parentId : 'root',
        onEnd(evt) {
            syncOrder(el, parentId);
        }
    });
}

function syncOrder(listEl, parentId) {
    const newOrder = Array.from(listEl.querySelectorAll(':scope > .menu-item')).map(li => li.dataset.id);
    if (parentId) {
        const parent = findItem(menuStructure, parentId);
        if (parent && parent.children) {
            parent.children = newOrder.map(id => parent.children.find(c => c.id === id)).filter(Boolean);
        }
    } else {
        menuStructure = newOrder.map(id => menuStructure.find(i => i.id === id)).filter(Boolean);
    }
    renderMenu();
}

// ─── CRUD Helpers ──────────────────────────────────────────────────────────────
function findItem(items, id) {
    for (const item of items) {
        if (item.id === id) return item;
        if (item.children) {
            const found = findItem(item.children, id);
            if (found) return found;
        }
    }
    return null;
}

function deleteItem(id) {
    menuStructure = menuStructure.filter(i => i.id !== id);
    menuStructure.forEach(i => {
        if (i.children) i.children = i.children.filter(c => c.id !== id);
    });
    renderMenu();
}

// ─── Add Item ──────────────────────────────────────────────────────────────────
document.getElementById('addMenuItemBtn').addEventListener('click', () => {
    const label  = document.getElementById('newItemLabel').value.trim();
    const route  = document.getElementById('newItemRoute').value.trim();
    const icon   = document.getElementById('newItemIcon').value.trim() || 'la la-folder';
    const type   = document.getElementById('newItemType').value;
    const parent = document.getElementById('newItemParent').value;
    const badge  = document.getElementById('newItemBadge').value.trim();
    const perm   = document.getElementById('newItemPermission').value.trim();
    const vis    = document.getElementById('newItemVisible').checked;

    if (!label) { alert('Label is required.'); return; }

    const newItem = {
        id: uid(), label, icon,
        route: route || '#',
        is_title: type === 'title',
        is_external: type === 'external',
        is_visible: vis,
        is_disabled: false,
        badge_count: badge,
        permissions: perm ? perm.split(',').map(s => s.trim()) : [],
        children: (type === 'submenu') ? [] : undefined,
    };

    if (parent) {
        const parentItem = findItem(menuStructure, parent);
        if (parentItem) {
            if (!parentItem.children) parentItem.children = [];
            parentItem.children.push(newItem);
        }
    } else {
        menuStructure.push(newItem);
    }

    // Clear form
    ['newItemLabel','newItemRoute','newItemBadge','newItemPermission'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('newItemParent').value = '';
    document.getElementById('newItemType').value   = 'link';
    renderMenu();
});

// ─── Edit Modal ────────────────────────────────────────────────────────────────
function openEditModal(id) {
    const item = findItem(menuStructure, id);
    if (!item) return;
    editingId = id;

    document.getElementById('editItemId').value          = id;
    document.getElementById('editItemLabel').value       = item.label;
    document.getElementById('editItemRoute').value       = item.route || '';
    document.getElementById('editItemIcon').value        = item.icon || '';
    document.getElementById('editItemBadge').value       = item.badge_count || '';
    document.getElementById('editItemColor').value       = item.color || '#ffffff';
    document.getElementById('editItemPermissions').value = (item.permissions || []).join(', ');
    document.getElementById('editItemVisible').checked   = item.is_visible !== false;
    document.getElementById('editItemDisabled').checked  = !!item.is_disabled;
    document.getElementById('editItemExternal').checked  = !!item.is_external;

    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

document.getElementById('saveEditItemBtn').addEventListener('click', () => {
    const item = findItem(menuStructure, editingId);
    if (!item) return;

    item.label       = document.getElementById('editItemLabel').value;
    item.route       = document.getElementById('editItemRoute').value;
    item.icon        = document.getElementById('editItemIcon').value;
    item.badge_count = document.getElementById('editItemBadge').value;
    item.color       = document.getElementById('editItemColor').value;
    item.permissions = document.getElementById('editItemPermissions').value.split(',').map(s => s.trim()).filter(Boolean);
    item.is_visible  = document.getElementById('editItemVisible').checked;
    item.is_disabled = document.getElementById('editItemDisabled').checked;
    item.is_external = document.getElementById('editItemExternal').checked;

    bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
    renderMenu();
});

// ─── Collapse All ──────────────────────────────────────────────────────────────
document.getElementById('collapseAllBtn').addEventListener('click', () => {
    document.querySelectorAll('.menu-item-children.open').forEach(ul => ul.classList.remove('open'));
});

// ─── Icon Preview ──────────────────────────────────────────────────────────────
document.getElementById('newItemIcon').addEventListener('input', function() {
    document.getElementById('iconPreviewEl').innerHTML = `<i class="${this.value}"></i>`;
});

// ─── Save ──────────────────────────────────────────────────────────────────────
document.getElementById('saveMenuBtn').addEventListener('click', async () => {
    const btn     = document.getElementById('saveMenuBtn');
    const btnText = btn.querySelector('.btn-text');
    const loading = btn.querySelector('.btn-loading');
    btnText.classList.add('d-none');
    loading.classList.remove('d-none');

    try {
        const res = await fetch(MENU_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ structure: menuStructure })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('settingsToast')?.querySelector('.toast-message')?.textContent && (document.getElementById('settingsToast').querySelector('.toast-message').textContent = 'Menu saved!');
            const toast = document.getElementById('settingsToast');
            if (toast) { toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 3000); }
        } else {
            alert(data.message || 'Failed to save.');
        }
    } catch(e) {
        alert('Network error. Please try again.');
    } finally {
        btnText.classList.remove('d-none');
        loading.classList.add('d-none');
    }
});

// ─── Reset ────────────────────────────────────────────────────────────────────
document.getElementById('resetMenuBtn').addEventListener('click', () => {
    if (!confirm('Reset menu to the default app structure? This removes all custom items.')) return;
    menuStructure = [];
    renderMenu();
});

// ─── Import Default (use hardcoded common items as a starting point) ───────────
document.getElementById('importDefaultMenuBtn').addEventListener('click', () => {
    if (!confirm('This will replace the current menu with the application default items. Continue?')) return;
    menuStructure = [
        { id: uid(), label: 'Dashboard', icon: 'la la-dashboard', route: 'dashboard', is_visible: true, is_title: false },
        { id: uid(), label: 'HR', icon: 'la la-users', is_title: true, is_visible: true },
        { id: uid(), label: 'Employees', icon: 'la la-users', route: '#', is_visible: true, is_title: false, children: [
            { id: uid(), label: 'All Employees', icon: 'la la-circle', route: 'employees.index', is_visible: true },
            { id: uid(), label: 'Departments', icon: 'la la-circle', route: 'departments.index', is_visible: true },
            { id: uid(), label: 'Designations', icon: 'la la-circle', route: 'designations.index', is_visible: true },
        ]},
        { id: uid(), label: 'Leave Management', icon: 'la la-calendar-check-o', route: '#', is_visible: true, is_title: false, children: [
            { id: uid(), label: 'Leave Requests', icon: 'la la-circle', route: 'leaverequests.index', is_visible: true },
            { id: uid(), label: 'My Leaves', icon: 'la la-circle', route: 'leaverequests.myleaverequests', is_visible: true },
            { id: uid(), label: 'Leave Types', icon: 'la la-circle', route: 'leavetypes.index', is_visible: true },
        ]},
        { id: uid(), label: 'Settings', icon: 'la la-cog', route: 'superadmin.settings.index', is_visible: true },
    ];
    renderMenu();
});

// ─── Init ─────────────────────────────────────────────────────────────────────
renderMenu();
</script>
@endpush
