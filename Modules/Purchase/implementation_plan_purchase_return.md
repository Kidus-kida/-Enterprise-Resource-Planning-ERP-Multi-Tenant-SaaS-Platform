
# Implementation Plan - Purchase Return Module

## Objective
Implement purchase return functionality in the `tewoshr` Purchase module, mirroring `erp.ettech.et`.

## Steps

1.  **Create Controller**
    *   Create `Modules/Purchase/app/Http/Controllers/PurchaseReturnController.php`.
    *   Adapt `index`, `add`, `store`, `show`, `destroy` methods from `erp.ettech.et`.
    *   Ensure proper namespacing (`Modules\Purchase\Http\Controllers`).
    *   Import necessary models (`Transaction`, `PurchaseLine`, `Account`, etc.) and utils (`TransactionUtil`, `ProductUtil`).

2.  **Define Routes**
    *   Update `Modules/Purchase/routes/web.php`.
    *   Add routes for `purchase-return` resource and `purchase-return/add/{id}`.

3.  **Create Views**
    *   Create `Modules/Purchase/resources/views/return/index.blade.php`.
    *   Create `Modules/Purchase/resources/views/return/create.blade.php` (renaming `add.blade.php` to `create.blade.php` for consistency, or keep as `add` if it's specific to returning from an existing purchase). Let's stick to `add` logic but maybe name it `add.blade.php` to avoid confusion with a generic create.
    *   Create `Modules/Purchase/resources/views/return/show.blade.php`.

4.  **Integration**
    *   Ensure the "Actions" dropdown in the Purchase List (`index.blade.php`) includes a "Return" button linking to `purchase-return/add/{id}`.

## Details

### Controller Adaptation
*   `erp.ettech.et` uses `App\Transaction`, `App\PurchaseLine`. In `tewoshr`, check if models are in `App\Models` or `Modules\...\Models`.
*   Verify `TransactionUtil` and `ProductUtil` availability in `tewoshr`.

### View Adaptation
*   Use `tewoshr` layout (`layouts.app` or similar).
*   Ensure components (like breadcrumbs, widgets) match `tewoshr` style.
