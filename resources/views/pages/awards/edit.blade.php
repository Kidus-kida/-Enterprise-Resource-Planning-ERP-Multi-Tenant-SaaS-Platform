<div class="modal-body">
    <form action="{{ route('awards.update', $award->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Title -->
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label required>{{ __('Title') }}</x-form.label>
                    <x-form.input type="text" id="title" name="title"
                        class="form-control @error('title') is-invalid @enderror" value="{{ $award->title }}" required/>
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="invalid-feedback" id="title-error"></div>

                </div>
            </div>

            <!-- Award Type -->
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label required>{{ __('Award Type') }}</x-form.label>
                    <x-form.input type="text" id="award_type" name="award_type" value="{{ $award->award_type }}"
                        required />
                    @error('award_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="invalid-feedback" id="title-error"></div>
                </div>
            </div>

            <!-- Award Date -->
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label required>{{ __('Awarded At') }}</x-form.label>
                    <x-form.input type="date" name="awarded_at"
                        class="form-control @error('awarded_at') is-invalid @enderror"
                        value="{{ $award->awarded_at }}"  required/>
                    @error('awarded_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="invalid-feedback" id="awarded_at-error"></div>
                </div>
            </div>

            <!-- Recipient (User) -->
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label required>{{ __('Recipient') }}</x-form.label>
                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror"
                        required>
                        <option value="">{{ __('Select User') }}</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}" 
                            {{ (old('user_id', $award->user_id) == $user->id) ? 'selected' : '' }}>
                            {{ $user->fullname ?? $user->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="invalid-feedback" id="user_id-error"></div>
                </div>
            </div>

            <!-- Awarded By -->
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label required>{{ __('Awarded By') }}</x-form.label>
                    <x-form.input type="text" id="awarded_by" name="awarded_by" 
                        class="form-control @error('title') is-invalid @enderror" value="{{ $award->awarded_by }}" required/>

                    @error('awarded_by')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="invalid-feedback" id="title-error"></div>
                </div>
            </div>

            <!-- Description -->
            <div class="col-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Description') }}</x-form.label>
                    <x-form.ckeditor name="description" id="editor"
                        class="form-control @error('description') is-invalid @enderror">
                        {{ $award->description }}
                    </x-form.ckeditor>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Certificate Upload -->
            <div class="col-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Award') }} <small class="text-info">{{ __('Upload award file')
                            }}</small></x-form.label>
                    <x-form.input type="file" id="certificate" name="certificate" value="{{ $award->awarded_by }}" accept=".pdf,.jpg,.jpeg,.png" />
                </div>
            </div>
        </div>

        <div class="submit-section my-2">
            <x-form.button class="btn btn-primary submit-btn" type="submit">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>


