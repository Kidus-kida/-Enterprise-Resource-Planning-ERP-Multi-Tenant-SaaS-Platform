<div class="dropdown dropdown-action">
    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="material-icons">more_vert</i>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="javascript:void(0)" data-url="{{ route('accounting.account-types.edit', $id) }}" data-ajax-modal="true" data-size="md" data-title="Edit Account Type">
            <i class="fa-solid fa-pencil m-r-5"></i> {{__('Edit')}}
        </a>
        <a class="dropdown-item deleteBtn" data-route="{{ route('accounting.account-types.destroy', $id) }}" href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i> {{__('Delete')}}
        </a>
    </div>
</div>
