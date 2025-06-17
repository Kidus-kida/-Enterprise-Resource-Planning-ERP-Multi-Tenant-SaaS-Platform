<x-table-action>
    @can('view-request')
        <a class="dropdown-item" href="{{ route('leaverequests.show', $id) }}"><i class="fa-solid fa-eye m-r-5"></i>
            {{ __('View') }}
        </a>
    @endcan
    @can('edit-request')
        <a class="dropdown-item" href="javascript:void(0)" data-url="{{ route('leaverequests.edit', $id) }}"
            data-ajax-modal="true" data-title="{{ __('Approve or Reject Leave Request') }}" data-size="lg"><i
                class="fa-solid fa-pencil m-r-5"></i>
            {{ __('Edit') }}
        </a>
    @endcan
    @can('delete-request')
        <a class="dropdown-item deleteBtn" data-route="{{ route('leaverequests.destroy', $id) }}"
            data-title="{{ __('Delete Leave Request') }}" data-question="{{ __('Are you sure you want to delete?') }}"
            href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i>
            {{ __('Delete') }}
        </a>
    @endcan
</x-table-action>
