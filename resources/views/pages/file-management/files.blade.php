@extends('layouts.app')

@section('page-content')
   <div class="content container-fluid">
         <x-breadcrumb class="col">
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a data-url="{{ route('files.create') }}" href="javascript:void(0)" class="btn add-btn"
                        data-ajax-modal="true"
                        
                        data-size="md" data-title="Add Files">
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
                   <h1 class="text-xl font-semibold text-gray-800">{{$folder->name}}</h1>
                </div>
            
            </div>

            {{-- @foreach ($folder->files as $file) --}}
            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        {{-- <h5 class="mb-1">{{ $file->title }}</h5>
                        <small class="text-muted">{{ $file->description }}</small> --}}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="" class="btn btn-outline-primary btn-sm">Edit</a>

                        <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            {{-- @endforeach --}}

        </div>
    </div>
</div>
@endsection
