@props([
    'name',
    'id' => $name,
    'class' => 'form-control'
])
@php
    $error = $errors->has($name) ? 'is-invalid' : '';
@endphp
<select name="{{ $name }}" id="{{ $id }}" {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</select>
@error($name)
<div class="invalid-feedback">
   {{ $message }}
</div>
@enderror