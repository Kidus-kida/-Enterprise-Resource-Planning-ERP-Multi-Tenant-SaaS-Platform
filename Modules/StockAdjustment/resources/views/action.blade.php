<div class="dropdown dropdown-action">
    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="material-icons">more_vert</i>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="javascript:void(0)" data-href="{{ route('stock_adjustment.show', $id) }}" data-container=".view_modal">
            <i class="fa-solid fa-eye m-r-5"></i> View
        </a>
        <a class="dropdown-item deleteBtn" data-route="{{ route('stock_adjustment.destroy', $id) }}" href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i> Delete
        </a>
    </div>
</div>
