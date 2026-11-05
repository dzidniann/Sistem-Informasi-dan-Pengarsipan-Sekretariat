@extends('legal.layout')
@section('content')
    @include('legal.notulensi.styles')
    <div class="legal-minutes">
        <div class="page-header">
            <h2 class="page-title">Edit Kegiatan</h2>
            <p class="page-subtitle">Perbarui kegiatan dan berkas dokumentasi.</p>
        </div>
        <div class="card">
            <form method="POST" action="{{ route('legal.activities.update', $record->id) }}" enctype="multipart/form-data">
                @include('legal.activities.fields')</form>
        </div>
    </div>
@endsection
