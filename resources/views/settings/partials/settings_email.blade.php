<div class="row">
    <h4 class="card-title text-primary">Email Settings</h4>
    <p class="text-muted">Configure email default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_driver</label>
            <select name="email_settings[mail_driver]" class="form-control select">
                <option value="smtp" {{ ($email_settings['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP
                </option>
                <!-- Add other drivers if needed -->
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_host</label>
            <input type="text" name="email_settings[mail_host]" class="form-control"
                value="{{ $email_settings['mail_host'] ?? '' }}" placeholder="mail_host">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_port</label>
            <input type="text" name="email_settings[mail_port]" class="form-control"
                value="{{ $email_settings['mail_port'] ?? '' }}" placeholder="mail_port">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_username</label>
            <input type="text" name="email_settings[mail_username]" class="form-control"
                value="{{ $email_settings['mail_username'] ?? '' }}" placeholder="mail_username">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_password</label>
            <input type="password" name="email_settings[mail_password]" class="form-control"
                value="{{ $email_settings['mail_password'] ?? '' }}" placeholder="mail_password">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_encryption</label>
            <input type="text" name="email_settings[mail_encryption]" class="form-control"
                value="{{ $email_settings['mail_encryption'] ?? '' }}" placeholder="mail_encryption_place">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_from_address</label>
            <input type="email" name="email_settings[mail_from_address]" class="form-control"
                value="{{ $email_settings['mail_from_address'] ?? '' }}" placeholder="mail_from_address">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">mail_from_name</label>
            <input type="text" name="email_settings[mail_from_name]" class="form-control"
                value="{{ $email_settings['mail_from_name'] ?? '' }}" placeholder="mail_from_name">
        </div>
    </div>
</div>
