@php($hasDocumentation = false)
@if ($activity->document)
    @php($hasDocumentation = true)
    <div>Dokumen hukum: {{ $activity->document->name }}</div>
@endif
@foreach (['dokumen_pendukung', 'foto_dokumentasi'] as $field)
    @if ($activity->$field)
        @php($hasDocumentation = true)
        <div>{{ str_contains($field, 'foto') ? 'Foto' : 'Pendukung' }}:
            {{ $activity->{$field . '_name'} ?? basename($activity->$field) }}</div>
    @endif
@endforeach
@unless ($hasDocumentation)
    -
@endunless
