<div>
    <label class="form-label" for="filter-cari">Cari Arsip</label><input id="filter-cari" class="form-control" name="cari"
        value="{{ request('cari') }}" placeholder="Pemohon, kegiatan, nomor surat...">
</div>
<div><label class="form-label" for="filter-tahun">Tahun</label><input id="filter-tahun" class="form-control" type="number"
        name="tahun" min="1900" max="2100" value="{{ request('tahun', $tahun ?? '') }}"
        placeholder="Semua tahun"></div>
<div><label class="form-label" for="filter-bulan">Bulan</label><select id="filter-bulan" class="form-control"
        name="bulan">
        <option value="">Semua bulan</option>
        @foreach (range(1, 12) as $number)
            <option value="{{ $number }}" @selected((int) request('bulan') === $number)>
                {{ \Carbon\Carbon::create(2000, $number, 1)->locale('id')->translatedFormat('F') }}</option>
        @endforeach
    </select>
</div>
<div><label class="form-label" for="filter-kategori">Kategori</label><select id="filter-kategori" class="form-control"
        name="kategori_id">
        <option value="">Semua kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(request('kategori_id') == $category->id)>{{ $category->nama }}</option>
        @endforeach
    </select>
</div>
<div><label class="form-label" for="filter-status">Status</label><select id="filter-status" class="form-control"
        name="status">
        <option value="">Semua status</option>
        @foreach (\App\Models\HumasCsr::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div><label class="form-label" for="filter-luaran">Luaran</label><select id="filter-luaran" class="form-control"
        name="luaran">
        <option value="">Semua luaran</option>
        @foreach (\App\Models\HumasCsr::LUARAN as $value => $label)
            <option value="{{ $value }}" @selected(request('luaran') === $value)>{{ $label }}</option>
        @endforeach
    </select></div>
<button class="btn btn-primary" type="submit">Tampilkan</button>
