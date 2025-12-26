<div class="dropdown dropdown-action">
    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item deleteBtn" data-route="{{ route('supplier-mappings.destroy', $id) }}" data-title="{{ __('Delete Mapping') }}"
            data-question="{{ __('Are you sure you want to delete?') }}" href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i>
            {{ __('Delete') }}
        </a>
    </div>
</div>
