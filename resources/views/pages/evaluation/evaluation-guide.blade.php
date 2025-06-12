@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-center text-primary mt-4">Evaluation Guide</h1>
        <a href="{{ route('evaluation.index') }}" class="btn btn-primary mb-4">
            &larr; Back to Evaluations
        </a>
        <iframe src="/pdfjs-viewer.html" width="100%"
            style="height:100vh; min-height:500px; border:none; box-shadow:0 2px 8px rgba(0,0,0,0.05); background:#f9f9f9;">
            This browser does not support PDFs. Please download the PDF to view it: <a href="/evaluation-guide.pdf">Download
                PDF</a>.
        </iframe>
    </div>
@endsection