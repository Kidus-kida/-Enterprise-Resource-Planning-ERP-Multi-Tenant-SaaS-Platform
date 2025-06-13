

@extends('layouts.app')

@section('page-content')
<div class="container mt-4">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('View File') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('files.show', $file->folder_id) }}">{{ __('File Management') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $file->title }}</li>
        </ul>
    </x-breadcrumb>

    <div class="card shadow rounded">
        <div class="card-body">

            <a href="{{ url()->previous() }}" class="btn btn-outline-warning mb-3">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>

            <h3 class="mb-3">{{ $file->title }}</h3>
            <p class="text-muted">{{ $file->description }}</p>

            @php
                $ext = pathinfo($file->path, PATHINFO_EXTENSION);
            @endphp

            @if(in_array($ext, ['pdf']))
                <iframe src="{{ asset('storage/' . $file->path) }}" width="100%" height="600px"></iframe>
            @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                <img src="{{ asset('storage/' . $file->path) }}" class="img-fluid" alt="File Preview">
            @else
                <div class="alert alert-info">
                    File preview not supported.
                    <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fa-solid fa-eye"></i> Open in New Tab
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
