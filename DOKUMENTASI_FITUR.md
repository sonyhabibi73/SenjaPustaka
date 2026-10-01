# SenjaPustaka — Feature & Flow Documentation

## What This Website Is

**SenjaPustaka** is a **digital library (e-library) platform**. Users can browse a book catalog, read books directly in the browser, review them, and earn points/badges for reading. Admins manage the whole catalog and user base.

**Stack:** Laravel (PHP 8.3) + Blade templates + vanilla JavaScript + Tailwind CSS 4 (Vite build) · SQLite/MySQL · PDF.js for reading · Pest for testing.

---

## How It Works (General Flow)

```
Browser request
  → Middleware (session, CSRF, security headers)
  → routes/web.php (auth check / admin check / rate limit)
  → Controller (query via Eloquent)
  → Blade view → HTML + JS assets
  → JavaScript handles interactions via fetch() (AJAX)
```

- **Page navigation** = normal full page load rendered by Blade.
- **Interactive actions** (save progress, favorite, bookmark, search suggestions, notification count) = AJAX calls with CSRF token.
- Every page belongs to one of four layouts: public/user (`app`), auth (`auth`), reader (`reader`), admin (`admin`).

---

## Features

### 1. Visitor Features (No Login)

| Feature                                       | What it does                                                                                                                                                                    |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Homepage**                                  | Hero + platform stats, "Continue Reading" (if logged in), trending books (by view count), popular categories, newest ebooks, random bookshelf                                   |
| **Collection / Catalog** (`/koleksi`)         | Browse books with search text, category filter, series filter (hidden while the series table is empty), sorting (newest / most popular / rating / title), paginated 12 per page |
| **Book Detail** (`/buku/{slug}`)              | Cover, metadata, rating, view counter (+1 per visit), related books, review list, favorite & read buttons                                                                       |
| **Search** (`/cari`)                          | Full search results page + live autocomplete (debounced 220ms, min 2 chars, max 6 results)                                                                                      |
| **Rankings** (`/peringkat`)                   | Top 20 books by average rating                                                                                                                                                  |
| **Reader Leaderboard** (`/peringkat-pembaca`) | Top 20 users by points                                                                                                                                                          |
| **Categories & Authors**                      | Index pages and detail pages for each category and author                                                                                                                       |
| **Static Pages**                              | About, Privacy Policy, Terms & Conditions                                                                                                                                       |
| **Contact Form**                              | Sends the message by email (rate-limited 5 per 10 min)                                                                                                                          |
| **Newsletter Signup**                         | Subscribe with email from the footer                                                                                                                                            |
| **Dark/Light Theme**                          | Saved in localStorage, follows system preference by default                                                                                                                     |
| **Visual Effects**                            | 3D card tilt, scroll reveal animations, count-up stats, animated progress bars, blinking stars, auto-hiding alerts                                                              |
| **PWA / Offline**                             | Service worker caches core pages; registered in production only **and never on `localhost` / `127.0.0.1` / `[::1]`** (a local build would otherwise be pinned to its own cache) |

### 2. Registered User Features

| Feature                       | What it does                                                                                                                                                        |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Register / Login / Logout** | Password min 12 chars (upper, lower, number, symbol), "remember me", session regeneration, login throttled 5/min                                                    |
| **Password Reset**            | Generic response (prevents user enumeration), throttled                                                                                                             |
| **Email Verification**        | Custom flow: SHA-256 hashed token stored in DB, expires in 24h, single-use, max 3 emails/hour                                                                       |
| **Profile**                   | Edit name, email, bio, avatar (auto-converted to WebP), change password (requires current password)                                                                 |
| **Dashboard**                 | Greeting by time of day, reading level card, statistics, annual reading goals, tabs (Reading / Finished / Favorites), streak & badges, personalized recommendations |
| **Reading Goals**             | Set yearly targets for books and pages                                                                                                                              |
| **Reader**                    | Read books in the browser (see section 3)                                                                                                                           |
| **Reading Progress**          | Saved automatically, resumes where you left off                                                                                                                     |
| **Bookmarks**                 | Add/remove a bookmark on a page, optionally with a note                                                                                                             |
| **Favorites**                 | Toggle favorite books with animated heart                                                                                                                           |
| **Reviews & Ratings**         | 1–5 star rating + comment, one review per user per book (resubmitting updates it), aggregate rating recalculated automatically                                      |
| **Notifications**             | In-app notifications (welcome, book finished, new badge), mark all as read, unread badge polled every 30s                                                           |
| **Newsletter Toggle**         | Enable/disable newsletter subscription from the dashboard                                                                                                           |

### 3. Reader (Core Feature)

One route (`/baca/{slug}`) auto-detects **three modes** from the book's file extension:

| Mode     | Source                      | Behavior                                                       |
| -------- | --------------------------- | -------------------------------------------------------------- |
| **Text** | `books.content` column      | Split into ~750-character pages, one page shown at a time      |
| **PDF**  | uploaded `.pdf`             | Rendered with PDF.js, continuous scroll, virtualized rendering |
| **CBZ**  | uploaded `.cbz` (image zip) | One `<img>` per page, next page preloaded                      |

**Key reader capabilities:**

- **Resume**: reopens at the last read page.
- **Auto-save progress**: debounced 600ms after page change, plus a `sendBeacon` safety net when closing the tab.
- **Zoom**: text font size (0.85–1.4rem) for text mode; 40%–400% with position anchoring for PDF.
- **Dark pages mode** for PDF.
- **Fullscreen** and **dark/light theme** toggle.
- **Bookmark** button inside the reader.
- **Auto-hiding toolbar** while scrolling.
- **Keyboard shortcuts**: arrows/space to navigate, `+`/`-`/`0` zoom, `D` dark mode, `F` fullscreen, `B` bookmark, `?` help, `Esc` close.
- **Performance tricks**: PDF served with HTTP Range requests (206) so only needed chunks download; only visible pages are rendered, off-screen canvases are released; CBZ page index cached 1 day with ETag/304 caching.

### 4. Gamification System

| Element             | How it works                                                                                                                                                                                                              |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Points**          | +2 per newly read page (capped at 30 pages per save to prevent exploits) + **50 bonus** when finishing a book                                                                                                             |
| **Level**           | 9 levels by points: _Pembaca Baru (0) → Penasaran (50) → Kutu Buku (150) → Pengembara Cerita (300) → Pencinta Kata (500) → Ahli Literasi (800) → Maestro Membaca (1200) → Legenda Perpustakaan (1800) → Dewa Baca (2500)_ |
| **Streak**          | Daily reading streak; resets if you skip a day; longest streak is tracked                                                                                                                                                 |
| **Badges**          | Earned automatically when hitting criteria: books finished, pages read, reviews, favorites, streak, distinct categories read                                                                                              |
| **Notifications**   | Triggered on badge earned and on finishing a book                                                                                                                                                                         |
| **Recommendations** | Picks your 3 most-read categories, suggests unread books from them (falls back to newest)                                                                                                                                 |

### 5. Admin Features (`/admin`, admin-only, 403 otherwise)

| Feature               | What it does                                                                                                                                                                                 |
| --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Dashboard**         | Counts of books, users, reviews, subscribers + top books, newest users, newest reviews                                                                                                       |
| **Book Management**   | Full CRUD: title, author, publisher, description, text content, cover color/image, **PDF/CBZ file upload (max 200MB)**, pages, year, language, featured/published flags, categories & series |
| **Catalog Entities**  | CRUD for categories, authors, publishers, series                                                                                                                                             |
| **Review Moderation** | List, search, and delete reviews (rating recalculated after delete)                                                                                                                          |
| **User Management**   | Search users, toggle admin role (cannot demote yourself, every change logged), delete users (cannot delete yourself)                                                                         |
| **Newsletter**        | View and manage subscribers                                                                                                                                                                  |
| **Activity Logs**     | Audit trail (action, user, IP, user agent, metadata) with filters by action/user/date range and a detail page                                                                                |

**Upload handling:** filenames are randomized (never client names), covers are converted to WebP 600px/q80, file extension is detected server-side (ZIP is forced to `.cbz` so the reader recognizes it), old files are deleted on replace.

---

## End-to-End Flows

### A. Reading a book

```
Homepage → Collection (filter/search) → Book detail (view +1)
  → Click "Read" → not logged in? redirect to login (returns to the book after)
  → Reader detects mode (text / pdf / cbz)
  → User turns pages → progress saved (debounced) → points, streak, badges updated
  → Finishing the book = +50 points + notification
  → Dashboard reflects new level, stats, and recommendations
```

### B. Registration to verification

```
POST /daftar (validate password + unique email)
  → create user (only name/email/password fillable → admin flag cannot be injected)
  → welcome notification → auto login → session regenerated → dashboard
POST /verifikasi-email/kirim → store SHA-256 token → send email link
GET /verifikasi-email/{token} → verify hash, check expiry & single-use → mark verified
```

### C. Admin adding a book

```
Login as admin → navbar "Admin" button → /admin/buku → "Add"
  → validate → unique slug (title-4random) → store cover as WebP → store file (pdf/cbz)
  → sync categories & series → book appears in catalog → immediately readable
```

### D. Review

```
Click stars on book page → POST /review {book_id, rating, comment}
  → upsert (one review per user per book)
  → recalc rating_avg & rating_count → check badges → JSON response updates UI in place
```

---

## Security Summary

- Security headers on every response: `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, full **Content-Security-Policy**.
- CSRF tokens on all forms and AJAX calls.
- Mass-assignment whitelisting (admin flag and points can never be set from user forms).
- Rate limiting on login, register, password reset, contact, and every AJAX endpoint.
- Eloquent parameter binding everywhere (no raw SQL).
- Password hashing, session regeneration, generic forgot-password response.
- Hashed + expiring + single-use email verification tokens.
- Failed logins logged; admin role changes logged; activity log stores IP + user agent.

## Performance Summary

- Eager loading to avoid N+1 queries, DB indexes on hot filters.
- PDF via HTTP Range (partial download) + virtualized rendering.
- CBZ index cached 1 day, pages served with ETag/304 + `immutable`.
- Lazy-loaded images with next-page preload.
- Debounced autosave (600ms) and search (220ms).
- PDF.js loaded only when a PDF is opened; all uploads converted to WebP.

## Operations & Testing

- `php artisan backup:database` — DB dump with optional gzip and rotation (default keep 7).
- `php artisan app:sync-sqlite-to-mysql` — safe upsert sync (or `--mirror` for full sync).
- 13 Pest test files covering auth, password reset, email verification, all pages, security (SQLi/XSS/mass-assignment/throttles), reader actions, PDF range requests, cover upload, and image optimization.
- `php artisan catalog:reseed [--force]` — wipe the catalog and plant the real one (see below).

---

## Demo engagement is a separate, optional step

`php artisan catalog:reseed` deletes books, pivots, placeholder authors/series/publishers **and** all engagement (reviews, reading progress, favorites, bookmarks — 51 / 7 / 2 / 0 rows at the time of writing), then replants the 14 books. It does **not** recreate engagement — the existing demo data is reset to zero on purpose.

`EngagementSeeder` (called by `DatabaseSeeder` on a fresh `db:seed`) creates demo reviews, reading progress, favorites, and a reading goal, and it only ever references real slugs. Re-add it at any time with:

```bash
php artisan db:seed --class=EngagementSeeder
```

---

## UI Design Direction — "Senja / Golden Hour"

The whole interface was rebuilt around a single idea: **the last warm light of day falling across paper.** It matches the name — _Senja_ (dusk) + _Pustaka_ (library). Everything lives in `resources/css/variables.css` (tokens v4), so every page, component, and admin screen inherits it.

### 1. Color — one paper, one ink, one ember

| Role                                  | Light                   | Dark                    | Use                                          |
| ------------------------------------- | ----------------------- | ----------------------- | -------------------------------------------- |
| `--color-bg`                          | `#f9f4ec` warm paper    | `#17130f` warm charcoal | page                                         |
| `--color-surface`                     | `#fffcf6`               | `#1f1a15`               | cards, inputs                                |
| `--color-surface-2`                   | `#f2eadd`               | `#282219`               | sunken panels                                |
| `--color-ink`                         | `#231a13`               | `#f2e9dc`               | body text                                    |
| `--color-muted`                       | `#6c5d50`               | `#ab9c8c`               | secondary text                               |
| `--color-primary`                     | `#2b1f16` sepia         | `#f2e9dc`               | dark buttons, admin rail                     |
| **`--color-accent`**                  | **`#b4491a` ember**     | **`#e0854a`**           | **the only CTA color**                       |
| `--color-field-border`                | `#9a8265`               | `#7a6c5a`               | form boundaries (≥3:1)                       |
| `--senja-sky`                         | `#fbf1e1`               | `#221c15`               | flat brand bands (reader wash, panels)       |
| `--dusk-1…4`                          | `#fdf7ee → #f2c191`     | `#17130f → #3f2a1d`     | **the sky**                                  |
| `--dusk-sun`                          | `#e0743a` **sun**       | `#ece4d4` **moon**      | the celestial disk                           |
| `--dusk-halo`                         | `#fce0b6` glare         | `#574734` halo          | flat ring around it                          |
| `--dusk-sun-tex`                      | `none`                  | crater gradients        | craters, dark only                           |
| `--dusk-starfield`                    | `none`                  | 5-star 88px tile        | small stars, dark only                       |
| `--spine-text`                        | `#f9f4ec`               | `#f3e2c8`               | foil lettering on book spines                |
| `--shelf-plank`                       | `#8a6647`               | `#2b2119`               | the shelf the books stand on                 |
| `--dusk-ground`                       | `#e0a070` terracotta    | `#241a12` umber         | flat ground band at the hero base            |
| `--dusk-ground-shade`                 | `rgba(122,62,26,.3)`    | `rgba(0,0,0,.55)`       | cast shadow under the shelf                  |
| `--hero-ground` / `--hero-pad-bottom` | `64px` / `80px`         | same                    | ground height + content clearance            |
| `--nav-offset` / `--nav-clearance`    | `14px` / `98px`         | same                    | floating navbar inset + reserved space       |
| `--nav-shadow` / `-scrolled`          | warm `rgba(74,48,22,…)` | `rgba(0,0,0,…)`         | floating bar elevation                       |
| `--px-star-x/y`                       | `0px` (JS)              | same                    | parallax offset, star layer (±6px)           |
| `--px-sun-x/y`                        | `0px` (JS)              | same                    | parallax offset, disk layer (±20px)          |
| `--px-sun-sink`                       | `0px` (JS)              | same                    | disk descent while the hero scrolls (0→36px) |

**Rules:** color only carries _meaning_ (danger/success/warn pastels) or _identity_ (a book's cover, the ember accent). No second accent, no neon, no decorative saturation.

**One sanctioned gradient:** `--dusk-1…4` is a vertical sky ramp, used **only** where the image being drawn is literally the sky — the homepage hero, the auth panel, the dash greeting. Cards, buttons, tables, and modals stay flat.

### 2. Type — three families, three jobs

- **Fraunces** (serif) — the literary voice: logo, headlines, book titles.
- **Inter** (sans) — the machine: buttons, labels, forms, tables.
- **JetBrains Mono** — numbers and metadata: stats, page counts, eyebrows, level numbers.

The serif never appears on a button or a table header. Uppercase mono eyebrows + a 28px ember rule are the recurring editorial tic.

### 3. Signature — the Senja touches

- **Floating sticky navbar.** `.navbar` is no longer a full-bleed bar welded to the top edge. It is `position: fixed`, **inset `14px` from the top**, sized to `--container` with side gutters, `12px` radius, a 1px border and a floating shadow. Content reserves `--nav-clearance: calc(nav-height + offset + 12px)` so nothing hides under it. Past 40px of scroll the existing `initNavbar()` adds `.is-scrolled` → the bar tightens to `60px`, rises to `8px`, its surface goes 90%→97% opaque and the shadow deepens. It never stops following the scroll, and the layout never jumps because the reserved space is constant.
- **Day ⇄ night is a different world, not just a palette swap.** Flipping `data-theme` changes the _subject_ of the picture:

|              | Mode terang (senja)                                       | Mode gelap (malam)                                                                                                         |
| ------------ | --------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| Sky          | paper `#fdf7ee` → gold `#f2c191`                          | charcoal `#17130f` → ember-brown `#3f2a1d`                                                                                 |
| Disk         | **sun** `#e0743a`, flat glare ring `#fce0b6`              | **moon** `#ece4d4` with **crater** gradients (`--dusk-sun-tex`) + halo `#574734`                                           |
| Stars        | none in the static field; only faint ember specks twinkle | **static starfield** — a 88×88 tile of 5 cream dots of varying size/opacity, `mask-image` fading it out toward the horizon |
| JS particles | `rgba(180,73,26,.42)` — the first stars of dusk           | `rgba(243,236,220,.9)` — full night stars                                                                                  |

Both disks come from the **same two elements** (`.bookshelf::before`, `.auth-panel::after`) driven purely by `--dusk-sun` / `--dusk-halo` / `--dusk-sun-tex` — no duplicated markup, no JS branch.

- **The sunset hero.** The homepage hero is a real dusk sky: `--dusk-1…4` ramping from paper at the top to gold at the horizon, capped by a **2px ember horizon line**, with the disk half-set behind the bookshelf — so the books read as silhouettes standing against the sun (or the moon).
- **The books stand on the ground.** A **flat** `--dusk-ground` band (`64px`, terracotta in light / umber in dark) fills the base of the hero, and the ember horizon line closes it as the boundary to the page. It is _not_ a gradient, so the single sanctioned ramp is untouched. The shelf plank gets a second, warm `box-shadow` that falls toward that ground — the books have weight instead of floating. The disk's `bottom` is derived as `calc(var(--hero-ground) - var(--hero-pad-bottom))`, so it always stops **exactly at the ground line** and never sinks into it (in light mode sun `#e0743a` and ground `#e0a070` only differ by 1.40:1 — sinking would erase it). The layout math is one token pair, so it stays correct at the `860px` breakpoint too.
- **The search card is a panel, not a lone input.** Under the field sit the **top 6 categories** as mono uppercase chips → `/kategori/{slug}`, separated by a hairline rule. `$categories` was already passed by `HomeController`, so this costs **zero extra queries**. It fills the band under the hero with _navigation_ rather than ornament, and the card now sits flush on the horizon instead of straddling it — which also stops the "Gulir" hint from colliding with the card's top edge.
- **Layered sky parallax — the horizon stays still.** `initHeroParallax()` (`main.js`) moves the sky in **three layers with three magnitudes**, because that contrast is what reads as depth rather than as a cursor gimmick:

| Layer                     | Distance | Amplitude          | Lerp                 |
| ------------------------- | -------- | ------------------ | -------------------- |
| Starfield `.hero-stars`   | farthest | ±6px               | `0.035` (lags most)  |
| Disk `.bookshelf::before` | middle   | ±20px x, ±10px y   | `0.06`               |
| Bookshelf `.bookshelf`    | nearest  | existing `js-tilt` | `0.12` (moves first) |

Three hard boundaries keep it from becoming page-wide wobble: (1) `pointermove` is listened for **on `.hero` only**, so it stops on its own above the search card — no magic numbers; (2) the **horizon line and shelf plank never move**, giving the eye a fixed reference; (3) `pointerleave` eases everything back to `0`. On top of that, `--px-sun-sink` lowers the disk `0 → 36px` as the hero scrolls out — this layer needs **no cursor**, so it is the one that stays alive on phones. Gated by `prefers-reduced-motion` and by `(pointer: fine)`; offsets are written as custom properties on `.hero`, never as transforms in JS.

- **The bookshelf.** Rigid bordered box removed; books now stand free on a warm wood plank with the sky showing through the gaps. Each spine is _cylindrical_ (light on the left edge, shadow on the right), carries a foil band at the head, and is tilted by a per-book `--rot` (±1.2° alternating, the last one leaning −5°) so the row breathes instead of looking like a table. Hover lifts **and** keeps the tilt.
- **Foil titles.** Spine lettering is cream `--spine-text` on the dusk spine colors (4.9–9.6:1). _The original used dark `--color-muted`, which measured 1.02–1.39:1 — invisible in light mode. That was the reported "tulisan hilang" bug._
- **Same sky everywhere.** Auth panel (vertical ramp + disk in the corner) and the dash greeting card (105° ramp, light behind the text, gold to the right) use the identical dusk tokens, so the brand reads as one continuous sunset — and one continuous night.
- **Logo**: Fraunces italic, with _Pustaka_ set in ember.
- **Active nav** = 2px ember underline; **active icon buttons** = ember border + icon.
- **Focus rings** are ember, so keyboard travel reads as a trail of light.
- **Bookshelf spines** use a warm dusk ladder (`--spine-1…6`: sepia, ochre, olive, terracotta, plum, bronze) instead of grey.
- **Level badge** and **stat icons** are flat ember on paper — the old pulsing amber glow was removed.

### 4. Geometry & motion

- 4px spacing scale; radii tightened to **3 / 5 / 8 / 12px** — printed, not pill-shaped.
- Shadows are warm-tinted and nearly absent (`0 2px 10px rgba(74,48,22,.07)` on hover only).
- Transitions 150–250ms, motion only ever conveys state.
- Focus, hover, active, disabled states exist on every control; `prefers-reduced-motion` kills all of it — including the hero parallax and its scroll sink.


### 6. Rebuild checklist (all passing)

- [x] Every hex in `resources/css/` resolves to a token (only the PDF page canvas stays white — it's paper).
- [x] No legacy colors (`#1c1c1a`, `#fbfbfa`, `#101b26`) anywhere in CSS, icons, manifest, or `theme-color`.
- [x] Light **and** dark mode contrast checked pair-by-pair, including every stop of the dusk sky.
- [x] Bookshelf no longer a bordered box; spine titles legible in both modes (was 1.02:1).
- [x] Floating navbar: reserved space `--nav-clearance` keeps `.main`, `scroll-padding-top`, and anchor jumps clear of it in both the 72px and 60px states.
- [x] Day/night verified: sun 1.9–2.2:1 vs sky, moon 10.7–12.0:1, craters 1.58:1, night stars 4.7–14.2:1, JS dusk specks 1.86:1; `--dusk-starfield` and `--dusk-sun-tex` are `none` in light mode.
- [x] Hero motion budget is capped at three layers (stars / disk / shelf) with a static horizon reference; the parallax never applies outside `.hero`, and is off under `prefers-reduced-motion` or `(pointer: coarse)`.
- [x] Hero base has a scene, not a void: flat ground band + cast shadow + `.scroll-hint` relocated onto it (7.7:1 light / 14.2:1 dark, was 5.03:1 on sky); no text sits outside its contrast-safe zone.
- [x] `.book-grid` never leaves a single orphan card: `$trending` and `$latest` are both **12**, which resolves to `6+6`, `5+5+2`, or `4+4+4` across all `auto-fill` widths (was 6 → `5+1` orphan at 1028–1231px).
- [x] The section directly under the search card uses `.section--lead` (48px, was 80px), closing the ~132px band below it.
- [x] `npm run build`, `npm run lint:check`, `npm run format:check`, `pint --test`, `php artisan test` (60/60) all pass.
- [x] Service worker cache bumped to `senja-v12` so returning users pick up the new skin.
- [x] Navigation honesty fixed: a failed navigation no longer falls back to the cached homepage. The SW now only serves a cached page when the URL matches **exactly**, otherwise it returns an explicit `503` offline page — so the address bar and the content can never disagree. Only `response.ok` (2xx) documents are cached, and `main.js` calls `registration.update()` on load so the fix is not held back by the browser's update throttle.
- [x] Service worker no longer registers on `localhost` / `127.0.0.1` / `[::1]` — a locally built bundle can no longer pin itself to its own cache.
- [x] Catalog replaced end to end: 14 real books, 13 categories, 6 authors, 4 publishers. Checked on `/`, `/koleksi` (both pages), `/peringkat`, `/kategori`, `/penulis`, `/cari`, `/dashboard`, `/buku/*`, `/baca/*` — zero placeholder titles anywhere.

---

## Notes

1. React/Inertia are installed in `package.json` but **not used** — the app runs entirely on Blade + vanilla JS.
2. `routes/api.php` is **not registered** in `bootstrap/app.php`, so the `/api/*` endpoints are currently inactive.
3. The reader loads its own JS file (`reader.js`) instead of `main.js`, so global features (notification polling, sidebar) don't run on reading pages.
4. No soft deletes — deleting a book/user permanently removes related data; use regular backups.
5. UI text is hard-coded in Indonesian (no localization layer yet).
