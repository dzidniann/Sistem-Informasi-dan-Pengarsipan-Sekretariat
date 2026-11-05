@extends($layout)

@section('content')
    <div class="page-container">

        <a href="{{ route('log-aktivitas.index') }}" style="color:#64748b; font-size:0.9rem;">
            &larr; Kembali ke daftar log
        </a>

        <div class="card">
            <div class="page-header">

                <h2 class="page-title">
                    {{ ucfirst($log->description) }}
                </h2>

                <p class="page-subtitle">
                    Oleh {{ $log->causer->name ?? '-' }}
                    pada {{ $log->created_at?->format('d M Y, H:i') ?? '-' }}
                    &middot;
                    Jenis data:
                    {{ class_basename($log->subject_type) ?? '-' }}
                    #{{ $log->subject_id ?? '-' }}
                </p>

            </div>
        </div>

        <div class="table-card">

            @php
                $properties = $log->properties;
                $attributes = $properties['attributes'] ?? [];
                $old = $properties['old'] ?? [];
            @endphp

            @if ($log->event === 'updated')

                <h3 class="section-title" style="margin-bottom:12px;">
                    Detail Perubahan
                </h3>

                @if (count($attributes) > 0)
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kolom</th>
                                    <th>Sebelum</th>
                                    <th>Sesudah</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($attributes as $kolom => $nilaiBaru)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ str_replace('_', ' ', ucfirst($kolom)) }}
                                            </strong>
                                        </td>

                                        <td style="background:#fef2f2; color:#b91c1c;">
                                            {{ $old[$kolom] ?? '(kosong)' }}
                                        </td>

                                        <td style="background:#ecfdf5; color:#166534;">
                                            {{ $nilaiBaru ?? '(kosong)' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="section-meta">
                        Tidak ada rincian perubahan yang tercatat untuk aktivitas ini.
                    </p>
                @endif
            @elseif($log->event === 'created')
                <h3 class="section-title" style="margin-bottom:12px;">
                    Data yang Ditambahkan
                </h3>

                @if (count($attributes) > 0)
                    <div class="table-wrapper">
                        <table class="table">
                            <tbody>

                                @foreach ($attributes as $kolom => $nilai)
                                    <tr>
                                        <td style="width:200px;">
                                            <strong>
                                                {{ str_replace('_', ' ', ucfirst($kolom)) }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $nilai ?? '(kosong)' }}
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="section-meta">
                        Tidak ada detail data yang tercatat.
                    </p>
                @endif
            @elseif($log->event === 'deleted')
                <h3 class="section-title" style="margin-bottom:12px;">
                    Data yang Dihapus
                </h3>

                @if (count($attributes) > 0)
                    <div class="table-wrapper">
                        <table class="table">
                            <tbody>

                                @foreach ($attributes as $kolom => $nilai)
                                    <tr>
                                        <td style="width:200px;">
                                            <strong>
                                                {{ str_replace('_', ' ', ucfirst($kolom)) }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $nilai ?? '(kosong)' }}
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="section-meta">
                        Tidak ada detail data yang tercatat.
                    </p>
                @endif
            @else
                <p class="section-meta">
                    Tidak ada detail tambahan untuk jenis aktivitas ini.
                </p>

            @endif

        </div>
    </div>
@endsection
