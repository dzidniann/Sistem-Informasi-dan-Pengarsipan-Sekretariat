<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
    @unless (($periodOnly ?? false) || ($hideSearch ?? false))
        <label>Cari judul, tanggal{{ $minutes ?? false ? ', atau notulis' : '' }}
            <input class="form-control" name="cari" value="{{ request('cari') }}" placeholder="Judul atau YYYY-MM-DD">
        </label>
    @endunless
    <label>Bulan
        <select class="form-control" name="bulan">
            @unless ($periodOnly ?? false)
                <option value="">Semua bulan</option>
            @endunless
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" @selected((string) ($bulan ?? request('bulan')) === (string) $m)>
                    {{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}</option>
            @endfor
        </select>
    </label>
    <label>Tahun
        <select class="form-control" name="tahun">
            @unless ($periodOnly ?? false)
                <option value="">Semua tahun</option>
            @endunless
            @php($selectedYear = (int) ($tahun ?? request('tahun', now()->year)))
            @foreach (collect(range(now()->year + 1, now()->year - 20))->push($selectedYear)->filter()->unique()->sortDesc() as $y)
                <option value="{{ $y }}" @selected((string) ($tahun ?? request('tahun')) === (string) $y)>{{ $y }}</option>
            @endforeach
        </select>
    </label>
    <button class="btn btn-primary">{{ $periodOnly ?? false ? 'Tampilkan Laporan' : 'Terapkan Filter' }}</button>
</div>
