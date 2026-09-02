/* ══════════════════════════════════════════════════════════════════
   READER — mode baca imersif (vanilla JS)
   - Teks / CBZ : navigasi halaman klasik
   - PDF        : PDF.js — scroll berkelanjutan ala Google Books,
                  zoom, halaman gelap (pageColors), progres + bookmark
   ══════════════════════════════════════════════════════════════════ */

import { createIcons, Bookmark, Keyboard, Maximize, Moon, Sun } from 'lucide';

/* Ikon toolbar reader (halaman ini tidak memuat main.js, jadi map sendiri). */
const readerIcons = { Bookmark, Keyboard, Maximize, Moon, Sun };

function renderIcons() {
    createIcons({ icons: readerIcons });
}

(() => {
    'use strict';

    const root = document.getElementById('reader-app');

    if (!root) {
        return;
    }

    renderIcons();

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    const bookId = Number(root.dataset.bookId);
    const mode = root.dataset.mode;
    let totalPages = Math.max(1, Number(root.dataset.pages));
    const startPage = Math.min(Number(root.dataset.startPage) || 1, totalPages);

    const textPages = [...document.querySelectorAll('.reader-page')];
    const cbzImages = [...document.querySelectorAll('.reader-cbz-img')];

    const pageLabel = document.getElementById('reader-page-label');
    const prevBtn = document.getElementById('reader-prev');
    const nextBtn = document.getElementById('reader-next');
    const progressFill = document.getElementById('reader-progress-fill');
    const toolbar = document.getElementById('reader-toolbar');
    const helpModal = document.getElementById('help-modal');
    const bookmarkBtn = document.getElementById('reader-bookmark');
    const fullscreenBtn = document.getElementById('reader-fullscreen');
    const themeBtn = document.getElementById('reader-theme');
    const zoomOutBtn = document.getElementById('reader-zoom-out');
    const zoomInBtn = document.getElementById('reader-zoom-in');
    const pdfZoomOut = document.getElementById('pdf-zoom-out');
    const pdfZoomIn = document.getElementById('pdf-zoom-in');
    const pdfZoomLevel = document.getElementById('pdf-zoom-level');
    const pdfDarkBtn = document.getElementById('pdf-dark');
    const pdfContainer = document.getElementById('pdf-viewer');

    let currentPage = startPage;
    let saveTimer = null;
    let hideTimer = null;
    let pdfApi = null;

    /* ── Tema gelap / terang (selaras dengan main.js) ─────────── */
    function initTheme() {
        const html = document.documentElement;
        const stored = localStorage.getItem('senja-theme');
        const system = window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
        html.dataset.theme = stored ?? system;

        if (!themeBtn) {
            return;
        }

        const applyIcon = () => {
            const dark = html.dataset.theme === 'dark';
            themeBtn.dataset.lucide = dark ? 'sun' : 'moon';
            themeBtn.setAttribute(
                'aria-label',
                dark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap',
            );
            renderIcons();
        };

        applyIcon();
        themeBtn.addEventListener('click', () => {
            const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
            html.dataset.theme = next;
            localStorage.setItem('senja-theme', next);
            applyIcon();
        });
    }

    /* ── Update halaman aktif (label, tombol, simpan progres) ── */
    function setCurrentPage(page) {
        const next = Math.max(1, Math.min(page, totalPages));

        if (next === currentPage) {
            return;
        }

        currentPage = next;

        if (pageLabel) {
            pageLabel.textContent = `${currentPage} / ${totalPages}`;
        }

        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        }

        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
        }

        scheduleSave();
    }

    /* ── Rendering satu halaman (mode teks / CBZ) ─────────────── */
    function render(page) {
        if (mode === 'pdf') {
            return;
        }

        setCurrentPage(page);

        if (mode === 'text') {
            textPages.forEach((el, index) => {
                el.hidden = index !== currentPage - 1;
            });
        }

        if (mode === 'cbz') {
            cbzImages.forEach((el) => {
                el.hidden = Number(el.dataset.page) !== currentPage;
            });

            // Preload halaman berikutnya supaya pindah halaman terasa instan
            // (gambar di-render sekaligus dipanaskan cache-nya).
            const next = cbzImages.find(
                (el) => Number(el.dataset.page) === currentPage + 1,
            );

            if (next) {
                const preload = new Image();
                preload.src = next.src;
            }
        }

        if (progressFill) {
            progressFill.style.width = `${(currentPage / totalPages) * 100}%`;
        }

        window.scrollTo({
            top: 0,
            behavior: prefersReducedMotion ? 'auto' : 'smooth',
        });
    }

    /* ── Simpan progres otomatis (debounce 600ms) ─────────────── */
    function scheduleSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveProgress, 600);
    }

    async function saveProgress() {
        const body = new FormData();
        body.append('book_id', String(bookId));
        body.append('page', String(currentPage));

        try {
            await fetch('/progres', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body,
            });
        } catch {
            /* progres tetap tersimpan lokal */
        }
    }

    /* ── Toolbar auto-hide saat scroll ────────────────────────── */
    let lastY = window.scrollY;
    window.addEventListener(
        'scroll',
        () => {
            const y = window.scrollY;

            clearTimeout(hideTimer);

            if (toolbar) {
                toolbar.classList.toggle(
                    'reader-toolbar--hidden',
                    y > lastY && y > 120,
                );
            }

            lastY = y;

            hideTimer = setTimeout(() => {
                if (toolbar) {
                    toolbar.classList.remove('reader-toolbar--hidden');
                }
            }, 2600);
        },
        { passive: true },
    );

    /* ── Bookmark ⭐ ──────────────────────────────────────────── */
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', async () => {
            const body = new FormData();
            body.append('book_id', String(bookId));
            body.append('page', String(currentPage));

            try {
                const res = await fetch('/bookmark', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    body,
                });
                const data = await res.json();
                bookmarkBtn.classList.toggle('is-active', data.bookmarked);
                bookmarkBtn.setAttribute(
                    'aria-label',
                    data.bookmarked ? 'Hapus bookmark' : 'Tambahkan bookmark',
                );
            } catch {
                /* diam */
            }
        });
    }

    /* ── Zoom (mode teks) ─────────────────────────────────────── */
    let fontSize = 1.06;

    function applyZoom() {
        textPages.forEach((el) => {
            el.style.fontSize = `${fontSize}rem`;
        });
    }

    zoomOutBtn?.addEventListener('click', () => {
        fontSize = Math.max(0.85, fontSize - 0.06);
        applyZoom();
    });

    zoomInBtn?.addEventListener('click', () => {
        fontSize = Math.min(1.4, fontSize + 0.06);
        applyZoom();
    });

    /* ── Fullscreen ───────────────────────────────────────────── */
    fullscreenBtn?.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            document.documentElement.requestFullscreen().catch(() => {});
        }
    });

    /* ── Navigasi (teks / CBZ) ────────────────────────────────── */
    if (mode !== 'pdf') {
        prevBtn?.addEventListener('click', () => render(currentPage - 1));
        nextBtn?.addEventListener('click', () => render(currentPage + 1));
    }

    /* ── Bantuan "?" ──────────────────────────────────────────── */
    const helpButtons = [
        document.getElementById('reader-help'),
        document.getElementById('reader-help-fab'),
    ];
    const helpClose = document.getElementById('help-close');

    helpButtons.forEach((btn) =>
        btn?.addEventListener('click', () =>
            helpModal?.classList.add('is-open'),
        ),
    );
    helpClose?.addEventListener('click', () =>
        helpModal?.classList.remove('is-open'),
    );
    helpModal?.addEventListener('click', (e) => {
        if (e.target === helpModal) {
            helpModal.classList.remove('is-open');
        }
    });

    /* ── PDF viewer (PDF.js, scroll berkelanjutan) ─────────────── */
    async function initPdfViewer() {
        if (!pdfContainer) {
            return;
        }

        const url = pdfContainer.dataset.url;
        const loadingEl = document.getElementById('pdf-loading');
        const errorEl = document.getElementById('pdf-error');
        const readerContent = document.querySelector('.reader-content');

        /* Polyfill kecil untuk browser yang belum punya API modern
           yang dipakai pdf.js v6 (Promise.withResolvers, URL.parse). */
        if (typeof Promise.withResolvers !== 'function') {
            Promise.withResolvers = () => {
                let resolve;
                let reject;
                const promise = new Promise((res, rej) => {
                    resolve = res;
                    reject = rej;
                });

                return { promise, resolve, reject };
            };
        }

        if (typeof URL.parse !== 'function') {
            URL.parse = (value, base) => {
                try {
                    return new URL(value, base);
                } catch {
                    return null;
                }
            };
        }

        const pagesEl = document.createElement('div');
        pagesEl.className = 'pdf-pages';
        pdfContainer.appendChild(pagesEl);

        const state = {
            doc: null,
            baseScale: 1,
            zoom: 1,
            dark: localStorage.getItem('senja-pdf-dark') === '1',
            items: [],
            heights: [],
            offsets: [],
        };

        const scale = () => state.baseScale * state.zoom;
        const clampZoom = (z) => Math.min(4, Math.max(0.4, z));
        const darkColors = {
            background: 'rgb(22, 30, 39)',
            foreground: 'rgb(236, 231, 220)',
        };
        const pageGap = 28; // jarak antar halaman di .pdf-pages

        const showError = () => {
            loadingEl?.classList.add('is-hidden');

            if (errorEl) {
                errorEl.hidden = false;
            }
        };

        /* ── Siapkan PDF.js + dokumen ─────────────────────────── */
        let pdfjs;

        try {
            pdfjs = await import('pdfjs-dist');
        } catch {
            showError();

            return;
        }

        pdfjs.GlobalWorkerOptions.workerSrc = new URL(
            'pdfjs-dist/build/pdf.worker.min.mjs',
            import.meta.url,
        ).toString();

        // disableAutoFetch: jangan pre-fetch seluruh file. pdf.js hanya
        // mengambil potongan data (range request) untuk halaman yang benar-
        // benar dirender — krusial untuk PDF berukuran puluhan MB.
        try {
            state.doc = await pdfjs.getDocument({ url, disableAutoFetch: true })
                .promise;
        } catch {
            showError();

            return;
        }

        totalPages = state.doc.numPages;
        currentPage = Math.min(startPage, totalPages);

        if (pageLabel) {
            pageLabel.textContent = `${currentPage} / ${totalPages}`;
        }

        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        }

        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
        }

        // kerangka halaman (div ringan, canvas diisi saat perlu)
        for (let i = 0; i < totalPages; i++) {
            const wrapper = document.createElement('div');
            wrapper.className = 'pdf-page';
            wrapper.dataset.pageIndex = String(i);
            const canvas = document.createElement('canvas');
            canvas.setAttribute('role', 'img');
            canvas.setAttribute('aria-label', `Halaman ${i + 1}`);
            wrapper.appendChild(canvas);
            pagesEl.appendChild(wrapper);
            state.items.push({
                wrapper,
                canvas,
                pdfPage: null,
                base: null,
                rendered: false,
                task: null,
            });
        }

        async function ensureBase(item) {
            if (item.base) {
                return;
            }

            const i = Number(item.wrapper.dataset.pageIndex);
            item.pdfPage = await state.doc.getPage(i + 1);
            item.base = item.pdfPage.getViewport({ scale: 1 });
        }

        function applyHeight(item) {
            const h = (item.base ? item.base.height : item.estHeight) * scale();
            item.wrapper.style.height = `${h}px`;
            state.heights[Number(item.wrapper.dataset.pageIndex)] = h;
        }

        function refreshOffsets() {
            let acc = 0;
            state.items.forEach((item, i) => {
                state.offsets[i] = acc;
                acc += (state.heights[i] ?? 0) + pageGap;
            });
        }

        // Skala dasar cukup dihitung dari halaman pertama — tidak perlu
        // memetakan viewport SELURUH halaman dulu (ini penyebab utama
        // render awal sangat lambat di buku ratusan halaman).
        try {
            await ensureBase(state.items[0]);
        } catch {
            showError();

            return;
        }

        const avail = Math.min(pdfContainer.clientWidth, 920) - 48;
        const firstBase = state.items[0].base;
        state.baseScale =
            avail > 0
                ? Math.max(0.5, Math.min(avail / firstBase.width, 1.8))
                : 1;

        // Tinggi sementara: asumsikan semua halaman seukuran halaman pertama
        // (umumnya memang begitu di novel) supaya scrollbar & layout langsung
        // stabil. Tinggi asli diisi bertahap di latar belakang.
        state.items.forEach((item) => {
            item.estHeight = firstBase.height;
        });
        state.items.forEach(applyHeight);
        refreshOffsets();

        /* ── Render satu halaman ke canvas ────────────────────── */
        async function renderItem(item) {
            if (item.rendered || item.task) {
                return;
            }

            try {
                if (!item.base) {
                    await ensureBase(item);
                }

                const viewport = item.pdfPage.getViewport({ scale: scale() });
                item.wrapper.style.height = `${viewport.height}px`;
                state.heights[Number(item.wrapper.dataset.pageIndex)] =
                    viewport.height;
                item.canvas.width = viewport.width;
                item.canvas.height = viewport.height;

                const params = { canvas: item.canvas, viewport };

                if (state.dark) {
                    params.pageColors = darkColors;
                }

                item.task = item.pdfPage.render(params);
                await item.task.promise;
                item.rendered = true;
            } catch (err) {
                if (err?.name !== 'RenderingCancelledException') {
                    console.error('Gagal merender halaman PDF:', err);
                }
            } finally {
                item.task = null;
            }
        }

        function releaseItem(item) {
            // batalkan render yang masih berjalan di luar layar
            if (item.task) {
                try {
                    item.task.cancel();
                } catch {
                    /* sudah selesai */
                }

                item.task = null;
            }

            if (!item.rendered) {
                return;
            }

            item.rendered = false;
            item.canvas.width = 0;
            item.canvas.height = 0;
        }

        function cancelRenders() {
            state.items.forEach(releaseItem);
        }

        /* ── Render halaman di (sekitar) layar ────────────────── */
        function renderVisible() {
            const vh = window.innerHeight;
            const buffer = vh * 1.2;
            const contentTop = readerContent.getBoundingClientRect().top;

            state.items.forEach((item, i) => {
                const top = contentTop + state.offsets[i];
                const bottom = top + (state.heights[i] ?? 0);

                if (top < vh + buffer && bottom > -buffer) {
                    renderItem(item);
                } else {
                    releaseItem(item);
                }
            });
        }

        /* ── Lacak halaman aktif + progres saat scroll ────────── */
        function trackPosition() {
            const vh = window.innerHeight;
            const mid = vh / 2;
            const contentTop = readerContent.getBoundingClientRect().top;

            let best = 0;
            let bestDist = Infinity;
            state.items.forEach((item, i) => {
                const top = contentTop + state.offsets[i];
                const center = top + (state.heights[i] ?? 0) / 2;
                const dist = Math.abs(center - mid);

                if (dist < bestDist) {
                    bestDist = dist;
                    best = i;
                }
            });
            setCurrentPage(best + 1);

            const maxScroll = document.documentElement.scrollHeight - vh;
            const pct =
                maxScroll > 0
                    ? Math.min(1, Math.max(0, window.scrollY / maxScroll))
                    : 0;

            if (progressFill) {
                progressFill.style.width = `${pct * 100}%`;
            }
        }

        let scrollTick = null;
        function onScroll() {
            if (scrollTick) {
                return;
            }

            scrollTick = requestAnimationFrame(() => {
                scrollTick = null;
                trackPosition();
                renderVisible();
            });
        }

        /* ── Zoom / ukuran ulang ──────────────────────────────── */
        function applyScale() {
            // jangkar halaman teratas agar posisi tetap saat zoom
            let anchorIndex = 0;
            let anchorTop = 0;

            for (let i = 0; i < state.items.length; i++) {
                const rect = state.items[i].wrapper.getBoundingClientRect();

                if (rect.bottom > 0) {
                    anchorIndex = i;
                    anchorTop = rect.top;
                    break;
                }
            }

            cancelRenders();
            state.items.forEach(applyHeight);
            refreshOffsets();

            const anchor = state.items[anchorIndex]?.wrapper;

            if (anchor) {
                const delta = anchor.getBoundingClientRect().top - anchorTop;
                window.scrollBy({ top: delta });
            }

            updateZoomLevel();
            renderVisible();
        }

        function updateZoomLevel() {
            if (pdfZoomLevel) {
                pdfZoomLevel.textContent = `${Math.round(state.zoom * 100)}%`;
            }
        }

        function zoomBy(factor) {
            state.zoom = clampZoom(state.zoom * factor);
            applyScale();
        }

        function resetZoom() {
            state.zoom = 1;
            applyScale();
        }

        function toggleDark() {
            state.dark = !state.dark;
            localStorage.setItem('senja-pdf-dark', state.dark ? '1' : '0');
            pdfContainer.classList.toggle('is-dark-pages', state.dark);

            if (pdfDarkBtn) {
                pdfDarkBtn.classList.toggle('is-active', state.dark);
                pdfDarkBtn.dataset.lucide = state.dark ? 'sun' : 'moon';
                pdfDarkBtn.setAttribute(
                    'aria-label',
                    state.dark ? 'Halaman terang' : 'Halaman gelap',
                );
                renderIcons();
            }

            cancelRenders();
            renderVisible();
        }

        function goTo(page) {
            const index = Math.max(0, Math.min(page - 1, totalPages - 1));

            if (!state.items[index]) {
                return;
            }

            const docTop =
                readerContent.getBoundingClientRect().top + window.scrollY;
            const top = docTop + state.offsets[index] - 64;
            window.scrollTo({
                top,
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
            });
            setCurrentPage(index + 1);
        }

        /* ── Kontrol UI ───────────────────────────────────────── */
        pdfZoomOut?.addEventListener('click', () => zoomBy(1 / 1.15));
        pdfZoomIn?.addEventListener('click', () => zoomBy(1.15));
        pdfZoomLevel?.addEventListener('click', resetZoom);
        pdfDarkBtn?.addEventListener('click', toggleDark);
        prevBtn?.addEventListener('click', () => goTo(currentPage - 1));
        nextBtn?.addEventListener('click', () => goTo(currentPage + 1));
        window.addEventListener('scroll', onScroll, { passive: true });

        let resizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const availResize =
                    Math.min(pdfContainer.clientWidth, 920) - 48;

                if (availResize > 0 && state.items[0]?.base) {
                    state.baseScale = Math.max(
                        0.5,
                        Math.min(availResize / state.items[0].base.width, 1.8),
                    );
                }

                applyScale();
            }, 200);
        });

        window.addEventListener('pagehide', () => {
            state.doc?.destroy();
        });

        pdfApi = { goTo, zoomBy, resetZoom, toggleDark };

        /* ── Mulai ────────────────────────────────────────────── */
        if (state.dark) {
            pdfContainer.classList.add('is-dark-pages');
        }

        if (pdfDarkBtn) {
            pdfDarkBtn.classList.toggle('is-active', state.dark);
            pdfDarkBtn.dataset.lucide = state.dark ? 'sun' : 'moon';
            pdfDarkBtn.setAttribute(
                'aria-label',
                state.dark ? 'Halaman terang' : 'Halaman gelap',
            );
            renderIcons();
        }

        loadingEl?.classList.add('is-hidden');
        updateZoomLevel();

        // lanjutkan dari halaman terakhir dibaca
        const resume = state.items[currentPage - 1]?.wrapper;

        if (resume) {
            window.scrollTo(
                0,
                resume.getBoundingClientRect().top + window.scrollY - 64,
            );
        }

        renderVisible();
        trackPosition();

        // Isi tinggi asli halaman lain di latar belakang secara bertahap,
        // supaya render awal tidak menunggu seluruh dokumen dipetakan.
        (async () => {
            for (let i = 1; i < state.items.length; i++) {
                const item = state.items[i];

                if (!item.base) {
                    try {
                        await ensureBase(item);
                    } catch {
                        /* halaman bermasalah — biarkan tinggi perkiraan */
                    }

                    applyHeight(item);
                    refreshOffsets();
                }

                // beri jeda supaya thread utama tetap responsif
                if (i % 10 === 0) {
                    await new Promise((r) => setTimeout(r, 0));
                }
            }

            // tinggi asli sudah lengkap → segarkan label & progres sekali lagi
            renderVisible();
            trackPosition();
        })();
    }

    /* ── Keyboard shortcuts (PDF) ─────────────────────────────── */
    function handlePdfKey(e) {
        // jangan rebut space dari tombol yang sedang fokus
        if (e.key === ' ' && document.activeElement?.tagName === 'BUTTON') {
            return;
        }

        switch (e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                pdfApi?.goTo(currentPage - 1);
                break;
            case 'ArrowRight':
                e.preventDefault();
                pdfApi?.goTo(currentPage + 1);
                break;
            case ' ':
            case 'PageDown':
                e.preventDefault();
                window.scrollBy({
                    top: window.innerHeight * 0.85,
                    behavior: prefersReducedMotion ? 'auto' : 'smooth',
                });
                break;
            case 'PageUp':
                e.preventDefault();
                window.scrollBy({
                    top: -window.innerHeight * 0.85,
                    behavior: prefersReducedMotion ? 'auto' : 'smooth',
                });
                break;
            case 'ArrowUp':
                e.preventDefault();
                window.scrollBy({ top: -56 });
                break;
            case 'ArrowDown':
                e.preventDefault();
                window.scrollBy({ top: 56 });
                break;
            case '+':
            case '=':
                pdfApi?.zoomBy(1.15);
                break;
            case '-':
            case '_':
                pdfApi?.zoomBy(1 / 1.15);
                break;
            case '0':
                pdfApi?.resetZoom();
                break;
            case 'd':
            case 'D':
                pdfApi?.toggleDark();
                break;
            case 'f':
            case 'F':
                fullscreenBtn?.click();
                break;
            case 'b':
            case 'B':
                bookmarkBtn?.click();
                break;
            case '?':
                helpButtons[0]?.click();
                break;
            default:
                break;
        }
    }

    /* ── Keyboard shortcuts (teks / CBZ) ──────────────────────── */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            helpModal?.classList.remove('is-open');

            return;
        }

        // saat modal bantuan terbuka, kunci navigasi lain
        if (helpModal?.classList.contains('is-open')) {
            return;
        }

        const tag = document.activeElement?.tagName;

        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
            return;
        }

        if (mode === 'pdf' && pdfContainer) {
            handlePdfKey(e);

            return;
        }

        switch (e.key) {
            case 'ArrowLeft':
                render(currentPage - 1);
                break;
            case 'ArrowRight':
            case ' ':
                e.preventDefault();
                render(currentPage + 1);
                break;
            case 'f':
            case 'F':
                fullscreenBtn?.click();
                break;
            case 'b':
            case 'B':
                bookmarkBtn?.click();
                break;
            case '?':
                helpButtons[0]?.click();
                break;
            default:
                break;
        }
    });

    /* ── Mulai ────────────────────────────────────────────────── */
    initTheme();

    if (mode === 'pdf') {
        initPdfViewer();
    } else {
        render(currentPage);
    }

    window.addEventListener('beforeunload', () => {
        navigator.sendBeacon?.(
            '/progres',
            new URLSearchParams({
                book_id: String(bookId),
                page: String(currentPage),
            }),
        );
    });
})();
