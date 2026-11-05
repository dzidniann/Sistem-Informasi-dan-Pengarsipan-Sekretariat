<dialog class="minutes-preview" id="minutes-preview" aria-labelledby="minutes-preview-title">
    <header>
        <h3 id="minutes-preview-title" style="margin:0;color:#0f172a;font-size:1.1rem;font-weight:500">Preview Berkas</h3>
        <div style="display:flex;gap:16px;align-items:center">
            <a id="minutes-preview-download" class="button button-primary" href="#">Unduh File</a>
            <button type="button" id="minutes-preview-close" aria-label="Tutup preview"
                style="border:1px solid #f1f5f9;background:#fff;border-radius:999px;width:48px;height:32px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center"><x-ui-icon
                    name="close" width="16" height="16" /></button>
        </div>
    </header>
    <div class="preview-content" id="minutes-preview-content"></div>
</dialog>
<script>
    (() => {
        const modal = document.getElementById('minutes-preview');
        const content = document.getElementById('minutes-preview-content');
        const download = document.getElementById('minutes-preview-download');
        document.querySelectorAll('[data-notulensi-preview]').forEach(link => {
            link.addEventListener('click', event => {
                if (!modal.showModal) return;
                event.preventDefault();
                const filename = link.dataset.filename || '';
                const extension = filename.split('.').pop().toLowerCase();
                const url = new URL(link.href);
                url.searchParams.set('download', '1');
                download.href = url.href;
                content.replaceChildren();
                if (extension === 'pdf') {
                    const frame = document.createElement('iframe');
                    frame.src = link.href;
                    frame.title = 'Preview PDF';
                    frame.style.cssText =
                        'width:100%;height:100%;border:0;background:white;border-radius:8px';
                    content.append(frame);
                } else if (['jpg', 'jpeg', 'png'].includes(extension)) {
                    const image = document.createElement('img');
                    image.src = link.href;
                    image.alt = 'Preview dokumentasi';
                    image.style.cssText =
                        'max-width:100%;max-height:100%;object-fit:contain;border-radius:8px';
                    image.addEventListener('error', () => {
                        content.textContent = 'Gambar tidak dapat ditampilkan.';
                    });
                    content.append(image);
                } else {
                    const box = document.createElement('div');
                    box.style.cssText = 'text-align:center;padding:40px;color:#64748b';
                    const title = document.createElement('h3');
                    title.textContent = 'Preview belum tersedia';
                    const description = document.createElement('p');
                    description.textContent = filename +
                        ' tidak dapat ditampilkan langsung oleh browser. Silakan download file untuk membukanya.';
                    const button = document.createElement('a');
                    button.href = url.href;
                    button.className = 'button button-primary';
                    button.textContent = 'Unduh File';
                    box.append(title, description, button);
                    content.append(box);
                }
                modal.showModal();
            });
        });
        document.getElementById('minutes-preview-close').addEventListener('click', () => modal.close());
        modal.addEventListener('click', event => {
            if (event.target === modal) modal.close();
        });
        modal.addEventListener('close', () => content.replaceChildren());
    })();
</script>
