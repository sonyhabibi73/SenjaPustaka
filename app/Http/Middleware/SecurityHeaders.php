<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection in browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy
        //
        // Saat `npm run dev` / `pnpm dev` berjalan, Vite menyajikan aset dari
        // origin-nya sendiri (http://127.0.0.1:5173) sehingga 'self' tidak
        // menutupinya — stylesheet, script, dan websocket HMR diblokir browser
        // dan halaman tampil tanpa CSS sama sekali. Origin dev ditambahkan HANYA
        // bila env=local dan file hot ada; /public/hot masuk .gitignore, dan
        // suite test memaksa APP_ENV=testing, jadi header produksi tidak berubah.
        $devHttp = '';
        $devWs = '';
        if (app()->isLocal()) {
            $vite = app(Vite::class);
            if ($vite->isRunningHot()) {
                $origin = rtrim(trim((string) file_get_contents($vite->hotFile())), '/');
                if ($origin !== '') {
                    $devHttp = " {$origin}";
                    $devWs = ' '.preg_replace('/^http/', 'ws', $origin);
                }
            }
        }

        $csp = "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$devHttp}; "
            ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$devHttp}; "
            ."font-src 'self' https://fonts.gstatic.com; "
            ."img-src 'self' data: blob:; "
            ."connect-src 'self'{$devHttp}{$devWs}; "
            ."frame-ancestors 'none'; "
            ."base-uri 'self'; "
            ."form-action 'self';";
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
