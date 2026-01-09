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

    <div class="col-lg-6">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>operations</th>
                    <th>keyboard_shortcut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>express_finalize:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][express_checkout]" class="form-control"
                            value="{{ $shortcuts['pos']['express_checkout'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>finalize:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][pay_n_ckeckout]" class="form-control"
                            value="{{ $shortcuts['pos']['pay_n_ckeckout'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>draft:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][draft]" class="form-control"
                            value="{{ $shortcuts['pos']['draft'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>cancel:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][cancel]" class="form-control"
                            value="{{ $shortcuts['pos']['cancel'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>recent_product_quantity:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][recent_product_quantity]" class="form-control"
                            value="{{ $shortcuts['pos']['recent_product_quantity'] ?? '' }}">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-lg-6">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>operations</th>
                    <th>keyboard_shortcut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>edit_discount:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][edit_discount]" class="form-control"
                            value="{{ $shortcuts['pos']['edit_discount'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>edit_order_tax:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][edit_order_tax]" class="form-control"
                            value="{{ $shortcuts['pos']['edit_order_tax'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>add_payment_row:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][add_payment_row]" class="form-control"
                            value="{{ $shortcuts['pos']['add_payment_row'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>finalize_payment:</td>
                    <td>
                        <input type="text" name="shortcuts[pos][finalize_payment]" class="form-control"
                            value="{{ $shortcuts['pos']['finalize_payment'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <td>add_new_product:</td>
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

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="disable_pay_checkout">disable_pay_checkout</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[disable_pay_checkout]"
                    id="disable_pay_checkout" value="1"
                    {{ !empty($pos_settings['disable_pay_checkout']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="disable_draft">disable_draft</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[disable_draft]"
                    id="disable_draft" value="1" {{ !empty($pos_settings['disable_draft']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="disable_express_checkout">disable_express_checkout</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[disable_express_checkout]"
                    id="disable_express_checkout" value="1"
                    {{ !empty($pos_settings['disable_express_checkout']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="hide_product_suggestion">hide_product_suggestion</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[hide_product_suggestion]"
                    id="hide_product_suggestion" value="1"
                    {{ !empty($pos_settings['hide_product_suggestion']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="hide_recent_trans">hide_recent_trans</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[hide_recent_trans]"
                    id="hide_recent_trans" value="1"
                    {{ !empty($pos_settings['hide_recent_trans']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="disable_discount">disable_discount</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[disable_discount]"
                    id="disable_discount" value="1"
                    {{ !empty($pos_settings['disable_discount']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="disable_order_tax">disable_order_tax</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[disable_order_tax]"
                    id="disable_order_tax" value="1"
                    {{ !empty($pos_settings['disable_order_tax']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="is_pos_subtotal_editable">subtotal_editable</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[is_pos_subtotal_editable]"
                    id="is_pos_subtotal_editable" value="1"
                    {{ !empty($pos_settings['is_pos_subtotal_editable']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="disable_suspend">disable_suspend_sale</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[disable_suspend]"
                    id="disable_suspend" value="1"
                    {{ !empty($pos_settings['disable_suspend']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>
