<div class="dropdown dropdown-action">
    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="material-icons">more_vert</i>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item view_modal" href="javascript:void(0)" data-href="{{ route('stock_adjustment.show', $id) }}">
            <i class="fa-solid fa-eye m-r-5"></i> View
        </a>
        <a class="dropdown-item deleteBtn" data-id="{{ $id }}" data-route="{{ route('stock_adjustment.destroy', $id) }}" 
            data-title="{{ __('Delete Stock Adjustment') }}" data-question="{{ __('Are you sure you want to delete?') }}" href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i> Delete
        </a>
    </div>
</div>
