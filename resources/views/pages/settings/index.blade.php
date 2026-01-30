@extends('layouts.app')

@section('sidebar')
    <x-custom-sidebar>
        {!! renderAppSettingsMenu() !!}
    </x-custom-sidebar>
@endsection

@section('page-content')
    <div class="content container-fluid pt-0">
        <!-- Page Header Section at the Absolute Top -->
        <div class="row m-0">
            <div class="col-12 p-0">
                @yield('page-header-section')
            </div>
        </div>

        <div class="row">
            <div class="col-12 p-0">
                @yield('page-section')
            </div>
        </div>
    </div>
@endsection
