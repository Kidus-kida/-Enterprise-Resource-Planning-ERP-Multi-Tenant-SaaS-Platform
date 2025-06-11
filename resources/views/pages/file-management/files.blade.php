@extends('layouts.app')

@section('page-content')
<div class="container mt-5">
    <div class="card shadow rounded">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <svg class="me-2" width="24" height="24" fill="currentColor" viewBox="0 0 24 24" style="color: #f1c40f;">
                        <path d="M10 4H2v16h20V6H12l-2-2z" />
                    </svg>
                    <h4 class="mb-0">ggg</h4>
                </div>
                <a href="" class="btn btn-primary btn-sm">
                    + New File
                </a>
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
