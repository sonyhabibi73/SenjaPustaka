/* ══════════════════════════════════════════════════════════════════
   SENJAPUSTAKA 2.0 — main.js (vanilla JS, zero library)
   ══════════════════════════════════════════════════════════════════ */

import {
    createIcons,
    AtSign,
    Atom,
    Award,
    Bell,
    BookCheck,
    BookMarked,
    BookOpen,
    Bookmark,
    Building2,
    Calendar,
    Camera,
    CircleCheck,
    Clock,
    Cpu,
    Crown,
    Eye,
    Feather,
    FileText,
    Flame,
    Globe,
    Heart,
    Home,
    Landmark,
    LayoutDashboard,
    Library,
    LibraryBig,
    Lock,
    LogIn,
    LogOut,
    Mail,
    MapPin,
    Medal,
    Moon,
    MoonStar,
    Palette,
    PartyPopper,
    Pencil,
    PenLine,
    Play,
    Plus,
    Rocket,
    Save,
    Search,
    Send,
    Sparkles,
    Sprout,
    PlugZap,
    Star,
    Sun,
    Sunset,
    Tags,
    Target,
    Telescope,
    Trash2,
    TrendingUp,
    TriangleAlert,
    Trophy,
    User,
    Users,
    Wrench,
} from 'lucide';

/* Ikon yang dipakai di seluruh halaman (tree-shaken, bukan full map). */
const lucideIcons = {
    AtSign,
    Atom,
    Award,
    Bell,
    BookCheck,
    BookMarked,
    BookOpen,
    Bookmark,
    Building2,
    Calendar,
    Camera,
    CircleCheck,
    Clock,
    Cpu,
    Crown,
    Eye,
    Feather,
    FileText,
    Flame,
    Globe,
    Heart,
    Home,
    Landmark,
    LayoutDashboard,
    Library,
    LibraryBig,
    Lock,
    LogIn,
    LogOut,
    Mail,
    MapPin,
    Medal,
    Moon,
    MoonStar,
    Palette,
    PartyPopper,
    Pencil,
    PenLine,
    Play,
    Plus,
    Rocket,
    Save,
    Search,
    Send,
    Sparkles,
    Sprout,
    PlugZap,
    Star,
    Sun,
    Sunset,
    Tags,
    Target,
    Telescope,
    Trash2,
    TrendingUp,
    TriangleAlert,
    Trophy,
    User,
    Users,
    Wrench,
};

(() => {
    'use strict';

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    /* ── Helper fetch dengan CSRF ─────────────────────────────── */
    async function postJSON(url, data = {}) {
        const body = new FormData();
        Object.entries(data).forEach(([key, value]) => body.append(key, value));

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body,
        });

        if (!res.ok) {
            throw new Error('Request gagal');
        }

        return res.json();
    }

    /* ── Tema gelap / terang ──────────────────────────────────── */
    function initTheme() {
        const html = document.documentElement;
        const stored = localStorage.getItem('senja-theme');
        const system = window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
        const theme = stored ?? system;
        html.dataset.theme = theme;

        const toggle = document.querySelector('.theme-toggle');
        const icon = toggle?.querySelector('.theme-icon');

        if (!toggle) {
            return;
        }

        const applyIcon = (mode) => {
            if (icon) {
                icon.dataset.lucide = mode === 'dark' ? 'moon' : 'sun';
                createIcons({ icons: lucideIcons });
            }
        };

        applyIcon(theme);
        toggle.setAttribute(
            'aria-label',
            theme === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap',
        );

        toggle.addEventListener('click', () => {
            const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
            html.dataset.theme = next;
            localStorage.setItem('senja-theme', next);
            toggle.classList.add('is-spinning');
            applyIcon(next);
            toggle.setAttribute(
                'aria-label',
                next === 'dark'
                    ? 'Ganti ke mode terang'
                    : 'Ganti ke mode gelap',
            );
            setTimeout(() => toggle.classList.remove('is-spinning'), 500);
        });
    }

    /* ── Navbar shrink on scroll ──────────────────────────────── */
    function initNavbar() {
        const navbar = document.querySelector('.navbar');

        if (!navbar) {
            return;
        }

        const onScroll = () => {
            navbar.classList.toggle('is-scrolled', window.scrollY > 40);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ── Sidebar mobile ───────────────────────────────────────── */
    function initSidebar() {
        const burger = document.querySelector('.burger');
        const sidebar = document.querySelector('.mobile-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        if (!burger || !sidebar) {
            return;
        }

        const close = () => {
            sidebar.classList.remove('is-open');
            overlay?.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
        };

        burger.addEventListener('click', () => {
            const open = sidebar.classList.toggle('is-open');
            overlay?.classList.toggle('is-open', open);
            burger.setAttribute('aria-expanded', String(open));
        });

        overlay?.addEventListener('click', close);
        sidebar
            .querySelectorAll('a')
            .forEach((link) => link.addEventListener('click', close));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                close();
            }
        });
    }

    /* ── Tilt 3D (lerp halus via requestAnimationFrame) ───────── */
    function initTilt() {
        if (prefersReducedMotion) {
            return;
        }

        const cards = document.querySelectorAll('.js-tilt');

        if (!cards.length) {
            return;
        }

        cards.forEach((card) => {
            let raf = null;
            let targetX = 0;
            let targetY = 0;
            let currentX = 0;
            let currentY = 0;

            const loop = () => {
                currentX += (targetX - currentX) * 0.12;
                currentY += (targetY - currentY) * 0.12;
                card.style.transform = `perspective(900px) rotateX(${currentY.toFixed(2)}deg) rotateY(${currentX.toFixed(2)}deg)`;

                if (
                    Math.abs(targetX - currentX) > 0.05 ||
                    Math.abs(targetY - currentY) > 0.05
                ) {
                    raf = requestAnimationFrame(loop);
                } else {
                    raf = null;
                }
            };

            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const px = (e.clientX - rect.left) / rect.width - 0.5;
                const py = (e.clientY - rect.top) / rect.height - 0.5;
                targetX = px * 8; // max ~4deg
                targetY = -py * 6;

                if (raf === null) {
                    raf = requestAnimationFrame(loop);
                }
            });

            card.addEventListener('mouseleave', () => {
                targetX = 0;
                targetY = 0;

                if (raf === null) {
                    raf = requestAnimationFrame(loop);
                }
            });
        });
    }

    /* ── Reveal on scroll ─────────────────────────────────────── */
    function initReveal() {
        const items = document.querySelectorAll('.reveal');

        if (!items.length) {
            return;
        }

        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            items.forEach((el) => el.classList.add('is-visible'));

            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
        );

        items.forEach((el) => observer.observe(el));
    }

    /* ── Bintang berkedip di hero/auth (partikel CSS murni) ───── */
    function initStars() {
        const containers = document.querySelectorAll('.hero-stars');

        if (!containers.length || prefersReducedMotion) {
            return;
        }

        containers.forEach((container) => {
            const count = 46;

            for (let i = 0; i < count; i++) {
                const star = document.createElement('i');
                const size = 1.5 + Math.random() * 2.5;
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--t', `${2.5 + Math.random() * 4}s`);
                star.style.setProperty('--td', `${Math.random() * 4}s`);
                container.appendChild(star);
            }
        });
    }

    /* ── Polling notifikasi (30 detik) ────────────────────────── */
    function initNotificationPolling() {
        const dot = document.querySelector('.icon-btn__dot[data-notif-dot]');

        if (!dot) {
            return;
        }

        const update = async () => {
            try {
                const data = await postJSON(
                    routeUrl('notifications.count', {}) ?? '/notifikasi/jumlah',
                );
                dot.style.display = data.count > 0 ? 'block' : 'none';
            } catch {
                /* diam saja */
            }
        };

        setInterval(update, 30000);
    }

    function routeUrl() {
        return '/notifikasi/jumlah';
    }

    /* ── Auto-hide alert ──────────────────────────────────────── */
    function initAlerts() {
        document.querySelectorAll('.alert').forEach((alert) => {
            setTimeout(() => {
                alert.style.transition =
                    'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                setTimeout(() => alert.remove(), 500);
            }, 5200);
        });
    }

    /* ── Search autosuggest ───────────────────────────────────── */
    function initSearchSuggest() {
        document.querySelectorAll('.js-search').forEach((input) => {
            const dropdown = input
                .closest('.search-card, .search-wrap')
                ?.querySelector('.search-suggest');

            if (!dropdown) {
                return;
            }

            let timer = null;

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const q = input.value.trim();

                if (q.length < 2) {
                    dropdown.classList.remove('is-open');

                    return;
                }

                timer = setTimeout(async () => {
                    try {
                        const res = await fetch(
                            `/cari/saran?q=${encodeURIComponent(q)}`,
                            {
                                headers: { Accept: 'application/json' },
                            },
                        );
                        const items = await res.json();
                        dropdown.innerHTML = '';

                        if (!items.length) {
                            dropdown.classList.remove('is-open');

                            return;
                        }

                        items.forEach((item) => {
                            const a = document.createElement('a');
                            a.href = item.url;
                            a.className = 'search-suggest__item';
                            a.innerHTML = `
                                <span class="search-suggest__swatch" style="background:${item.cover_color}"></span>
                                <span><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.author ?? '')}</span></span>`;
                            dropdown.appendChild(a);
                        });

                        dropdown.classList.add('is-open');
                    } catch {
                        dropdown.classList.remove('is-open');
                    }
                }, 220);
            });

            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && e.target !== input) {
                    dropdown.classList.remove('is-open');
                }
            });
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    /* ── Heart favorit + bookmark ────────────────────────────── */
    function initFavorite() {
        document
            .querySelectorAll('.js-favorite, .js-favorite-bookmark')
            .forEach((btn) => {
                const isBookmark = btn.classList.contains(
                    'js-favorite-bookmark',
                );

                btn.addEventListener('click', async () => {
                    const bookId = btn.dataset.bookId;

                    try {
                        const data = await postJSON(btn.dataset.url, {
                            book_id: bookId,
                        });
                        const active = isBookmark
                            ? data.bookmarked
                            : data.favorited;

                        btn.classList.add('is-pop');
                        btn.classList.toggle('is-active', active);

                        const burst = btn.querySelector('.burst');

                        if (burst && active) {
                            // createIcons sudah mengubah <i data-lucide> jadi <svg>
                            // (elemen tetap menyimpan atribut data-lucide)
                            burst
                                .querySelectorAll('[data-lucide]')
                                .forEach((i, index) => {
                                    const angle = (index / 7) * Math.PI * 2;
                                    i.style.setProperty(
                                        '--dx',
                                        `${Math.cos(angle) * 26}px`,
                                    );
                                    i.style.setProperty(
                                        '--dy',
                                        `${Math.sin(angle) * 26}px`,
                                    );
                                });
                        }

                        setTimeout(() => btn.classList.remove('is-pop'), 500);
                    } catch {
                        /* gagal: diam */
                    }
                });
            });
    }

    /* ── Rating bintang interaktif ────────────────────────────── */
    function initStarsInput() {
        document.querySelectorAll('.stars--input').forEach((widget) => {
            const input = document.getElementById(widget.dataset.target);

            if (!input) {
                return;
            }

            const stars = widget.querySelectorAll('.star');
            const apply = (value) => {
                stars.forEach((star) => {
                    const on = Number(star.dataset.value) <= Number(value);
                    star.textContent = on ? '★' : '☆';
                    star.classList.toggle('is-popped', false);
                });
            };
            stars.forEach((star) => {
                star.addEventListener('mouseenter', () =>
                    apply(star.dataset.value),
                );
                star.addEventListener('click', () => {
                    input.value = star.dataset.value;
                    apply(star.dataset.value);
                    star.classList.add('is-popped');
                    const form = input.closest('form');

                    if (form && typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    }
                });
            });

            widget.addEventListener('mouseleave', () =>
                apply(input.value || 0),
            );
        });
    }

    /* ── Tabs dashboard ───────────────────────────────────────── */
    function initTabs() {
        document.querySelectorAll('.js-tabs').forEach((group) => {
            const buttons = group.querySelectorAll('.tab-btn');
            buttons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    buttons.forEach((b) => b.classList.remove('is-active'));
                    btn.classList.add('is-active');

                    group
                        .closest('.js-tabs-wrap')
                        ?.querySelectorAll('.tab-pane')
                        .forEach((pane) =>
                            pane.classList.toggle(
                                'is-active',
                                pane.dataset.tab === btn.dataset.tab,
                            ),
                        );
                });
            });
        });
    }

    /* ── Count-up angka ───────────────────────────────────────── */
    function initCountUp() {
        const els = document.querySelectorAll('.count-up');

        if (!els.length || prefersReducedMotion) {
            els.forEach((el) => {
                el.textContent = el.dataset.count ?? el.textContent;
            });

            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    const el = entry.target;
                    const raw = String(
                        el.dataset.count ?? el.textContent,
                    ).replace(/\D/g, '');
                    const target = Number(raw) || 0;
                    const duration = 900;
                    const start = performance.now();

                    const tick = (now) => {
                        const progress = Math.min(1, (now - start) / duration);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        el.textContent = Math.round(
                            target * eased,
                        ).toLocaleString('id-ID');

                        if (progress < 1) {
                            requestAnimationFrame(tick);
                        }
                    };

                    requestAnimationFrame(tick);
                    observer.unobserve(el);
                });
            },
            { threshold: 0.4 },
        );

        els.forEach((el) => observer.observe(el));
    }

    /* ── Progress bar animasi ─────────────────────────────────── */
    function initProgressBars() {
        document.querySelectorAll('.progress[data-percent]').forEach((bar) => {
            const fill = bar.querySelector('.progress__bar');
            const percent = bar.dataset.percent;

            if (!fill) {
                return;
            }

            if (prefersReducedMotion) {
                fill.style.width = `${percent}%`;

                return;
            }

            const observer = new IntersectionObserver(
                (entries) => {
                    if (entries[0].isIntersecting) {
                        setTimeout(() => {
                            fill.style.width = `${percent}%`;
                        }, 120);
                        observer.disconnect();
                    }
                },
                { threshold: 0.2 },
            );

            observer.observe(bar);
        });
    }

    /* ── Init berdasarkan halaman ─────────────────────────────── */
    function initPage() {
        const page = document.body.dataset.page;

        if (page === 'home') {
            initStars();
        }

        if (page === 'login' || page === 'register') {
            initStars();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initTheme();
        initNavbar();
        initSidebar();
        initTilt();
        initReveal();
        initNotificationPolling();
        initAlerts();
        initSearchSuggest();
        initFavorite();
        initStarsInput();
        initTabs();
        initCountUp();
        initProgressBars();
        initPage();

        // Ganti ikon emoji → Lucide (<i data-lucide> → <svg>)
        createIcons({ icons: lucideIcons });

        // Service worker (PWA) — hanya di production
        if ('serviceWorker' in navigator && import.meta.env?.PROD) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    });
})();
