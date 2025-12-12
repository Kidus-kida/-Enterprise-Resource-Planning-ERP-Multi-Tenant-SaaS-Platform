@props([
    'name',
    'id' => $name,
    'class' => 'form-control'
])

<select name="{{ $name }}" id="{{ $id }}" {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</select>