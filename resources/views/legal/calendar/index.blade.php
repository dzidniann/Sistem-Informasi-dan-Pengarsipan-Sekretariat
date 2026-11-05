@extends('legal.layout')

@section('content')
    @php
        $currentDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        $daysInMonth = $currentDate->daysInMonth;
        $startDayOfWeek = $currentDate->dayOfWeekIso; // 1 = Senin, 7 = Minggu
    @endphp

    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Agenda Konsultasi & Review Dokumen</p>
            <h1 class="page-title">Kalender Jadwal Hukum</h1>
        </div>
        <div class="calendar-navigation" style="display:flex; align-items:center; gap:8px;">
            <a href="{{ route('legal.calendar.index', ['bulan' => $prevMonth->month, 'tahun' => $prevMonth->year]) }}"
                class="btn btn-secondary btn-sm">
                <x-ui-icon name="chevron-left" width="16" height="16" /> {{ $prevMonth->translatedFormat('M Y') }}
            </a>
            <a href="{{ route('legal.calendar.index') }}" class="btn btn-primary btn-sm">
                Hari Ini
            </a>
            <a href="{{ route('legal.calendar.index', ['bulan' => $nextMonth->month, 'tahun' => $nextMonth->year]) }}"
                class="btn btn-secondary btn-sm">
                {{ $nextMonth->translatedFormat('M Y') }} <x-ui-icon name="chevron-right" width="16" height="16" />
            </a>
        </div>
    </div>

    <div class="legal-detail-grid">
        <!-- Calendar Grid -->
        <div class="card calendar-card" style="padding:20px;">
            <div class="calendar-heading" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin:0; font-size:1.3rem; font-weight:700; color:#0f172a;">
                    {{ $currentDate->translatedFormat('F Y') }}
                </h2>
                <span style="font-size:0.85rem; color:#64748b;">
                    {{ $schedules->count() + $reviewDocuments->count() }} agenda terjadwal
                </span>
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px; font-size:0.8rem;">
                <span style="color:#0f766e;">Konsultasi terjadwal</span>
                <span style="color:#92400e;">Jadwal Review Dokumen</span>
            </div>

            <div class="calendar-grid" style="text-align:center; margin-bottom:8px;">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                    <div
                        style="font-size:0.78rem; font-weight:700; color:#64748b; padding:6px 0; text-transform:uppercase;">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            <div class="calendar-grid calendar-days">
                {{-- Empty cells before start day --}}
                @for ($i = 1; $i < $startDayOfWeek; $i++)
                    <div style="background:#f8fafc; border-radius:10px; min-height:80px; padding:6px; opacity:0.3;"></div>
                @endfor

                {{-- Day cells --}}
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                        $daySchedules = $schedules->filter(function ($s) use ($dateStr) {
                            return $s->schedule && $s->schedule->format('Y-m-d') === $dateStr;
                        });
                        $dayReviews = $reviewDocuments->filter(fn ($document) => $document->review_date->format('Y-m-d') === $dateStr);
                        $isToday = now()->format('Y-m-d') === $dateStr;
                    @endphp
                    <div
                        style="background:{{ $isToday ? '#e6f7f4' : '#fff' }}; border:1px solid {{ $isToday ? '#0f766e' : '#e2e8f0' }}; border-radius:10px; min-height:85px; padding:6px; display:flex; flex-direction:column; gap:4px; text-align:left;">
                        <span
                            style="font-size:0.82rem; font-weight:{{ $isToday ? '800' : '600' }}; color:{{ $isToday ? '#115e59' : '#0f172a' }};">
                            {{ $day }} {{ $isToday ? '•' : '' }}
                        </span>
                        @foreach ($daySchedules as $sched)
                            <a href="{{ route('legal.consultations.show', $sched) }}"
                                style="display:block; background:#0f766e; color:#fff; border-radius:6px; padding:2px 5px; font-size:0.7rem; font-weight:600; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; text-decoration:none;"
                                title="{{ $sched->title }} ({{ $sched->schedule->format('H:i') }})">
                                {{ $sched->schedule->format('H:i') }} {{ Str::limit($sched->title, 12) }}
                            </a>
                        @endforeach
                        @foreach ($dayReviews as $document)
                            <a href="{{ route('legal.documents.show', $document) }}"
                                style="display:block; background:#fef3c7; color:#92400e; border:1px solid #fcd34d; border-radius:6px; padding:2px 5px; font-size:0.7rem; font-weight:600; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; text-decoration:none;"
                                title="Jadwal Review: {{ $document->name }}">
                                Review: {{ Str::limit($document->name, 16) }}
                            </a>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>

        <!-- Upcoming consultations and pending document reviews -->
        <div
            role="region"
            aria-label="Daftar agenda dan dokumen perlu review"
            tabindex="0"
            style="display:flex; flex-direction:column; gap:20px; max-height:min(75vh, 760px); min-height:0; overflow-y:auto; overscroll-behavior-y:contain; scrollbar-gutter:stable; padding-right:4px;"
        >
            <div class="card">
                <h3 class="card-title" style="margin-bottom:6px;">Agenda 7 Hari ke Depan</h3>
                <p class="card-meta" style="margin-bottom:16px;">Jadwal konsultasi dan review dokumen terdekat</p>

                @forelse($upcoming as $item)
                    <div
                        style="padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <strong style="font-size:0.9rem; color:#0f172a;">{{ $item->title }}</strong>
                            <span class="badge badge-published" style="font-size:0.7rem;">
                                {{ $item->schedule->format('H:i') }}
                            </span>
                        </div>
                        <div style="font-size:0.8rem; color:#64748b; margin-top:4px;">
                            Pemohon: <strong>{{ $item->user->name ?? '-' }}</strong>
                        </div>
                        <div style="font-size:0.78rem; color:#115e59; margin-top:2px;">
                            {{ $item->schedule->translatedFormat('l, d F Y') }}
                        </div>
                        <div style="margin-top:8px; text-align:right;">
                            <a href="{{ route('legal.consultations.show', $item) }}" class="btn btn-secondary btn-sm"
                                style="padding:4px 10px; font-size:0.75rem;">
                                Buka Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:30px 10px; color:#94a3b8;">
                        <p style="font-size:0.88rem; margin:0;">Tidak ada jadwal konsultasi dalam 7 hari mendatang.</p>
                    </div>
                @endforelse

                <h4 class="card-title" style="margin:20px 0 12px;">Jadwal Review Dokumen</h4>
                @forelse ($upcomingReviews as $document)
                    <div style="padding:12px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px; margin-bottom:10px; overflow-wrap:anywhere;">
                        <strong>{{ $document->name }}</strong>
                        <p class="card-meta" style="margin:6px 0;">{{ $document->review_date->translatedFormat('l, d F Y') }}</p>
                        <a class="btn btn-secondary btn-sm" href="{{ route('legal.documents.show', $document) }}">Buka Dokumen</a>
                    </div>
                @empty
                    <p class="card-meta">Tidak ada jadwal review dokumen dalam 7 hari mendatang.</p>
                @endforelse
            </div>
            <div class="card">
                <h3 class="card-title">Dokumen Perlu Review ({{ $pendingCount }})</h3>
                <p class="card-meta" style="margin-bottom:16px;">Diurutkan berdasarkan tanggal review. Dokumen tanpa tanggal tetap ditampilkan di daftar ini.</p>
                @forelse ($pendingReviews as $document)
                    <div style="padding:12px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px; margin-bottom:10px; overflow-wrap:anywhere;">
                        <strong>{{ $document->name }}</strong>
                        <p class="card-meta" style="margin:6px 0;">
                            @if ($document->review_date)
                                {{ $document->review_date->translatedFormat('d F Y') }}
                                @if ($document->review_date->lt(today()))
                                    <span class="badge badge-expired">Lewat jadwal</span>
                                @endif
                            @else
                                Tanggal review belum diisi
                            @endif
                        </p>
                        <a class="btn btn-secondary btn-sm" href="{{ route('legal.documents.show', $document) }}">Buka Dokumen</a>
                    </div>
                @empty
                    <p class="card-meta">Tidak ada dokumen berstatus Perlu Review.</p>
                @endforelse
                @if ($pendingCount > 20)
                    <a class="btn btn-secondary btn-sm" href="{{ route('legal.documents.index', ['status' => 'NEED_REVIEW']) }}">Lihat Semua Dokumen Perlu Review</a>
                @endif
            </div>
        </div>
    </div>
@endsection
