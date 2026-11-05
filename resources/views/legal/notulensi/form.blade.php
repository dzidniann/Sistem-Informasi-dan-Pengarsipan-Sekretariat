@extends('legal.layout')
@section('content')
    @include('legal.notulensi.styles')
    <div class="legal-minutes">
        <div class="page-header">
            <h2 class="page-title">Edit Notulensi</h2>
            <p class="page-subtitle">Perbarui notulensi rapat dan berkas dokumentasi.</p>
        </div>
        <div class="card">
            <form method="POST" action="{{ route('legal.notulensi.update', $record->id) }}" enctype="multipart/form-data">
                @include('legal.notulensi.fields')</form>
        </div>
    </div>
@endsection
