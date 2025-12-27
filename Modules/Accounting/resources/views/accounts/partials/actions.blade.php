<div class="btn-group btn-group-xs" style="white-space: nowrap;">

    <button data-href="{{ route('accounts.edit', $account->id) }}" class="btn btn-outline-primary btn-modal"
        data-container=".account_model">
        <i class="fa fa-pencil"></i>
    </button>

    <a href="{{ route('accounting.account_book', $account->id) }}" class="btn btn-outline-warning">
        <i class="fa fa-book"></i>
    </a>

    <button type="button" class="btn btn-outline-info btn-modal"
        data-href="{{ route('accounting.account_transfer', $account->id) }}" data-container=".view_modal">
        <i class="fa fa-exchange"></i>
    </button>


    {{-- <a href="{{ route('accounting.account_transfer', $account->id) }}" class="btn btn-outline-info btn-modal">
        <i class="fa fa-exchange"></i>
    </a> --}}

    <button data-href="" class="btn btn-outline-success btn-modal" data-container=".view_modal">
        <i class="fa fa-money"></i>
    </button>

    @if ($account->is_closed)
        <button class="btn btn-outline-success activate_account" data-id="{{ $account->id }}">
            <i class="fa fa-check"></i>
        </button>
    @else
        <button class="btn btn-outline-danger close_account" data-id="{{ $account->id }}">
            <i class="fa fa-times"></i>
        </button>
    @endif

</div>
