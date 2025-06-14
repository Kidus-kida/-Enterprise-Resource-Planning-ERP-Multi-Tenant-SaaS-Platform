<x-table-action>
    @can('Approve or Reject Leave Request')
        <a class="dropdown-item" href="javascript:void(0)" data-url="{{ route('leaverequests.edit', $id) }}"
            data-ajax-modal="true" data-title="{{ __('Approve or Reject Leave Request') }}" data-size="lg"><i
                class="fa-solid fa-pencil m-r-5"></i>
            {{ __('Edit') }}
        </a>
    @endcan
    @can('delete-ticket')
        <a class="dropdown-item deleteBtn" data-route="{{ route('leaverequests.destroy', $id) }}"
            data-title="{{ __('Delete Ticket') }}" data-question="{{ __('Are you sure you want to delete?') }}"
            href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i>
            {{ __('Delete') }}
        </a>
    @endcan
</x-table-action>
