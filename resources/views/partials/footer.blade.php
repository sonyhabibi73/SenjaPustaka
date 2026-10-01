<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer__inner">
            <div>
                <a href="{{ route('home') }}" class="logo" aria-label="SenjaPustaka - Beranda">Senja<em>Pustaka</em></a>
                <p style="margin-top:14px;">Rak baca digital untuk ebook PDF dan CBZ. Baca kapan pun, di mana pun, kumpulkan poin, dapatkan badge, dan temukan cerita yang menemani senjamu.</p>
            </div>

            <nav aria-label="Navigasi footer - Jelajahi">
                <h4>Jelajahi</h4>
                <ul class="footer__links">
                    <li><a href="{{ route('library') }}">Koleksi Buku</a></li>
                    <li><a href="{{ route('ranking') }}">Peringkat Buku</a></li>
                    <li><a href="{{ route('leaderboard') }}">Peringkat Pembaca</a></li>
                    <li><a href="{{ route('categories.index') }}">Kategori</a></li>
                    <li><a href="{{ route('authors.index') }}">Penulis</a></li>
                </ul>
            </nav>

            <nav aria-label="Navigasi footer - Perusahaan">
                <h4>Perusahaan</h4>
                <ul class="footer__links">
                    <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                    <li><a href="{{ route('contact') }}">Kontak</a></li>
                    <li><a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a></li>
                    <li><a href="{{ route('legal.terms') }}">Syarat &amp; Ketentuan</a></li>
                    <li><a href="{{ route('register') }}">Daftar Gratis</a></li>
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                </ul>
            </nav>

            <div>
                <h4>Newsletter</h4>
                <p>Dapatkan rekomendasi buku pilihan setiap minggu, langsung di inbox-mu.</p>
                <form method="POST" action="{{ route('newsletter.subscribe') }}" class="newsletter-form" aria-label="Form berlangganan newsletter">
                    @csrf
                    <label for="footer-newsletter-email" class="sr-only">Alamat email</label>
                    <input type="email" id="footer-newsletter-email" name="email" class="input" placeholder="email@kamu.com" required>
                    <button type="submit" class="btn btn--primary" aria-label="Berlangganan newsletter"><i data-lucide="mail" aria-hidden="true"></i></button>
                </form>
            </div>
        </div>

        <div class="footer__bottom">
            <span>© {{ now()->year }} SenjaPustaka. Dibuat dengan <i data-lucide="heart" class="text-amber" aria-hidden="true"></i> di Indonesia.</span>
            <span>Baca kapan saja, progresmu selalu menunggu di halaman terakhir.</span>
        </div>
    </div>
</footer>
