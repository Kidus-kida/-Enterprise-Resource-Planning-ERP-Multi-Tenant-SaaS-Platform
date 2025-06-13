<x-table-action>
    
    <a class="dropdown-item deleteBtn" data-route="{{ route('files.destroy', $id) }}" data-title="{{ __('Delete Folder') }}"
        data-question="{{ __('Are you sure you want to delete?') }}" href="javascript:void(0)">
        <i class="fa-regular fa-trash-can m-r-5"></i>
        {{ __('Delete') }}
    </a>
</x-table-action>






