<div class="row">
    <div class="col-sm-12">
        <h4 class="card-title text-primary">Add keyboard shortcuts</h4>
        <p class="text-muted">shortcut_help; example: <b>ctrl+shift+b</b>, <b>ctrl+h</b></p>
        <p class="text-muted">
            <b>available_key_names_are:</b>
            <br> shift, ctrl, alt, backspace, tab, enter, return, capslock, esc, escape, space, pageup, pagedown, end,
            home,
            <br>left, up, right, down, ins, del, and plus
        </p>
    </div>

    <div class="col-md-6">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Operations</th>
                    <th>Keyboard Shortcut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Express Checkout:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][express_checkout]" class="form-control"
                            value="{{ $shortcuts['pos']['express_checkout'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Pay & Checkout:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][pay_n_ckeckout]" class="form-control"
                            value="{{ $shortcuts['pos']['pay_n_ckeckout'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Draft:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][draft]" class="form-control"
                            value="{{ $shortcuts['pos']['draft'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Cancel:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][cancel]" class="form-control"
                            value="{{ $shortcuts['pos']['cancel'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Go to product quantity:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][recent_product_quantity]" class="form-control"
                            value="{{ $shortcuts['pos']['recent_product_quantity'] ?? '' }}">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-md-6">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Operations</th>
                    <th>Keyboard Shortcut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Edit Discount:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][edit_discount]" class="form-control"
                            value="{{ $shortcuts['pos']['edit_discount'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Edit Order Tax:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][edit_order_tax]" class="form-control"
                            value="{{ $shortcuts['pos']['edit_order_tax'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Add Payment Row:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][add_payment_row]" class="form-control"
                            value="{{ $shortcuts['pos']['add_payment_row'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Finalize Payment:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][finalize_payment]" class="form-control"
                            value="{{ $shortcuts['pos']['finalize_payment'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>Add new product:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][add_new_product]" class="form-control"
                            value="{{ $shortcuts['pos']['add_new_product'] ?? '' }}">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-sm-12 mt-4">
        <h4>pos_settings</h4>
    </div>

    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[disable_pay_checkout]"
                id="disable_pay_checkout" value="1"
                {{ !empty($pos_settings['disable_pay_checkout']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="disable_pay_checkout">disable_pay_checkout</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[disable_draft]"
                id="disable_draft" value="1" {{ !empty($pos_settings['disable_draft']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="disable_draft">disable_draft</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[disable_express_checkout]"
                id="disable_express_checkout" value="1"
                {{ !empty($pos_settings['disable_express_checkout']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="disable_express_checkout">disable_express_checkout</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[hide_product_suggestion]"
                id="hide_product_suggestion" value="1"
                {{ !empty($pos_settings['hide_product_suggestion']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="hide_product_suggestion">hide_product_suggestion</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[hide_recent_trans]"
                id="hide_recent_trans" value="1"
                {{ !empty($pos_settings['hide_recent_trans']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="hide_recent_trans">hide_recent_trans</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[disable_discount]"
                id="disable_discount" value="1"
                {{ !empty($pos_settings['disable_discount']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="disable_discount">disable_discount</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[disable_order_tax]"
                id="disable_order_tax" value="1"
                {{ !empty($pos_settings['disable_order_tax']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="disable_order_tax">disable_order_tax</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[is_pos_subtotal_editable]"
                id="is_pos_subtotal_editable" value="1"
                {{ !empty($pos_settings['is_pos_subtotal_editable']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="is_pos_subtotal_editable">subtotal_editable</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="pos_settings[disable_suspend]"
                id="disable_suspend" value="1"
                {{ !empty($pos_settings['disable_suspend']) ? 'checked' : '' }}>
            <label class="form-check-label mb-0 ms-2" for="disable_suspend">disable_suspend_sale</label>
        </div>
    </div>
</div>
