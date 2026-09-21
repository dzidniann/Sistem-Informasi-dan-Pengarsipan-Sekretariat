@extends('sekretariat.layout')

@section('content')

    <div class="page-header">
        <h2 class="page-title">Laporan Bulanan</h2>
        <p class="page-subtitle">Pilih bulan dan tahun untuk melihat ringkasan laporan arsip.</p>
    </div>

    <div class="card" style="max-width: 620px;">
        <form method="GET" action="{{ route('laporan.generate') }}">
            <label class="form-label">Bulan</label>
            <select name="bulan" required class="form-control">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                @endfor
            </select>

            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" value="{{ date('Y') }}" required class="form-control">

            <button type="submit" class="button button-primary" style="margin-top: 16px;">Tampilkan</button>
        </form>
    </div>
@endsection

