<x-table-action>
    @can('edit-tax_range')
        <a class="dropdown-item"
           href="javascript:void(0)"
           data-url="{{ route('payroll.tax.edit', $id) }}"
           data-ajax-modal="true"
           data-title="Edit Tax Range"
           data-size="md">
            <i class="fa-solid fa-pencil m-r-5"></i>Edit
        </a>
    @endcan

    @can('show-tax_range')
        <a class="dropdown-item"
           href="{{ route('payroll.tax.show', $id) }}">
           <i class="fa-solid fa-eye m-r-5"></i>View
        </a>
    @endcan

    @can('delete-tax_range')
        <a class="dropdown-item deleteBtn"
           data-route="{{ route('payroll.tax.destroy', $id) }}"
           data-title="Delete Tax Range"
           data-question="Are you sure?"
           href="javascript:void(0)">
            <i class="fa-regular fa-trash-can m-r-5"></i>Delete
        </a>
    @endcan
</x-table-action>
