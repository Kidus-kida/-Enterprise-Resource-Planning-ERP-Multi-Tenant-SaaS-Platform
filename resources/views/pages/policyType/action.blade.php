<x-table-action>
    @can('edit-user')
    <a class="dropdown-item" href="javascript:void(0)" data-url="{{ route('policy-type.edit', $id) }}" data-ajax-modal="true"
        data-title="Edit Policy Type" data-size="lg"><i class="fa-solid fa-pencil m-r-5"></i>
        {{ __('Edit') }}
    </a>
    @endcan
    @can('delete-user')
    <a class="dropdown-item deleteBtn" data-route="{{ route('policy-type.destroy', $id) }}" data-title="Delete Policy Type"
        data-question="Are you sure you want to delete?" href="javascript:void(0)">
        <i class="fa-regular fa-trash-can m-r-5"></i>
        {{ __('Delete') }}
    </a>
    @endcan
</x-table-action>
