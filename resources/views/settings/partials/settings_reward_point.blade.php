<div class="row">
    <h4 class="card-title text-primary">Reward Point Settings</h4>
    <p class="text-muted">Configure reward point default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_rp">enable_rp</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_rp" id="enable_rp" value="1"
                    {{ optional($business)->enable_rp == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-12 enable_rp {{ optional($business)->enable_rp == 1 ? '' : 'd-none' }}">
        <h4>earning_points_setting</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">rp_name</label>
                    <input type="text" name="rp_name" class="form-control" value="{{ optional($business)->rp_name }}"
                        placeholder="rp_name">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">amount_for_unit_rp</label>
                    <input type="text" name="amount_for_unit_rp" class="form-control"
                        value="{{ optional($business)->amount_for_unit_rp }}" placeholder="amount_for_unit_rp">
                    <small class="text-muted">amount_for_unit_rp_tooltip</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">min_order_total_for_rp</label>
                    <input type="text" name="min_order_total_for_rp" class="form-control"
                        value="{{ optional($business)->min_order_total_for_rp }}" placeholder="min_order_total_for_rp">
                    <small class="text-muted">min_order_total_for_rp_tooltip</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">max_RP_per_order</label>
                    <input type="text" name="max_rp_per_order" class="form-control"
                        value="{{ optional($business)->max_rp_per_order }}" placeholder="max_RP_per_order">
                    <small class="text-muted">max_RP_per_order_tooltip</small>
                </div>
            </div>
        </div>

        <h4>redeem_points_setting</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">redeem_amount_per_unit_rp</label>
                    <input type="text" name="redeem_amount_per_unit_rp" class="form-control"
                        value="{{ optional($business)->redeem_amount_per_unit_rp }}" placeholder="redeem_amount_per_unit_rp">
                    <small class="text-muted">redeem_amount_per_unit_rp_tooltip</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">min_order_total_for_redeem</label>
                    <input type="text" name="min_order_total_for_redeem" class="form-control"
                        value="{{ optional($business)->min_order_total_for_redeem }}" placeholder="min_order_total_for_redeem">
                    <small class="text-muted">min_order_total_for_redeem_tooltip</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">min_redeem_point</label>
                    <input type="text" name="min_redeem_point" class="form-control"
                        value="{{ optional($business)->min_redeem_point }}" placeholder="min_redeem_point">
                    <small class="text-muted">min_redeem_point_tooltip</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">max_redeem_point</label>
                    <input type="text" name="max_redeem_point" class="form-control"
                        value="{{ optional($business)->max_redeem_point }}" placeholder="max_redeem_point">
                    <small class="text-muted">max_redeem_point_tooltip</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">rp_expiry_period</label>
                    <div class="input-group">
                        <input type="number" name="rp_expiry_period" class="form-control"
                            value="{{ optional($business)->rp_expiry_period }}" placeholder="rp_expiry_period">
                        <select name="rp_expiry_type" class="form-control select">
                            <option value="month" {{ optional($business)->rp_expiry_type == 'month' ? 'selected' : '' }}>
                                month</option>
                            <option value="year" {{ optional($business)->rp_expiry_type == 'year' ? 'selected' : '' }}>
                                year</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
