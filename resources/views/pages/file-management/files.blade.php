
@extends('layouts.app')

@section('page-content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Add Files') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('File Management') }}
            </li>
        </ul>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <a data-url="{{ route('files.create', ['folder' => $folder->id]) }}" 
                   href="javascript:void(0)" 
                   class="btn add-btn"
                   data-ajax-modal="true"
                   data-size="md" 
                   data-title="Add Files">
                   <i class="fa-solid fa-plus"></i> {{ __('Add Files') }}
                </a>
            </div>
        </x-slot>
    </x-breadcrumb>

    <div class="card shadow rounded">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <svg class="me-2" width="24" height="24" fill="currentColor" viewBox="0 0 24 24" style="color: #f1c40f;">
                        <path d="M10 4H2v16h20V6H12l-2-2z" />
                    </svg>
                    <h1 class="text-xl font-semibold text-gray-800">{{ $folder->name }}</h1>
                </div>
            </div>

            @forelse ($folder->files as $file)
            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $file->title }}</h5>
                        <small class="text-muted">{{ $file->description }}</small>
                        <div class="file-actions mt-2">
                           
                            {{-- <a href="{{ asset('storage/'.$file->path) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-info me-2">
                               <i class="fa-regular fa-eye"></i> View
                            </a> --}}
                            <a href="{{ route('files.view', $file->id) }}" class="btn btn-sm btn-outline-info me-2">
    <i class="fa-regular fa-eye"></i> View
</a>

                            
                            
                            <a href="{{ route('files.download', $file->id) }}" 
                               class="btn btn-sm btn-outline-success">
                               <i class="fa-solid fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('files.edit', $file->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </a>
                        <form action="{{ route('files.destroy', $file->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fa-regular fa-trash-can"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="alert alert-info">No files found in this folder</div>
            @endforelse
        </div>
    </div>
</div>
@endsection