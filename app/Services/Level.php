<?php

namespace App\Services;

class Level
{
    /**
     * 9 level pembaca, dari "Pembaca Baru" sampai "Dewa Baca".
     *
     * @var array<int, array{name: string, points: int}>
     */
    public const LEVELS = [
        1 => ['name' => 'Pembaca Baru', 'points' => 0],
        2 => ['name' => 'Penasaran', 'points' => 50],
        3 => ['name' => 'Kutu Buku', 'points' => 150],
        4 => ['name' => 'Pengembara Cerita', 'points' => 300],
        5 => ['name' => 'Pencinta Kata', 'points' => 500],
        6 => ['name' => 'Ahli Literasi', 'points' => 800],
        7 => ['name' => 'Maestro Membaca', 'points' => 1200],
        8 => ['name' => 'Legenda Perpustakaan', 'points' => 1800],
        9 => ['name' => 'Dewa Baca', 'points' => 2500],
    ];

    /**
     * Hitung level, progres, dan target berikutnya berdasarkan poin.
     *
     * @return array{
     *     number: int,
     *     name: string,
     *     points: int,
     *     current_threshold: int,
     *     next: array{name: string, points: int, remaining: int}|null,
     *     progress: int
     * }
     */
    public static function for(int $points): array
    {
        $currentLevel = 1;
        $current = self::LEVELS[1];

        foreach (self::LEVELS as $level => $info) {
            if ($points >= $info['points']) {
                $currentLevel = $level;
                $current = $info;
            } else {
                break;
            }
        }

        $next = self::LEVELS[$currentLevel + 1] ?? null;
        $currentThreshold = $current['points'];
        $nextThreshold = $next['points'] ?? $currentThreshold;

        $progress = $next === null
            ? 100
            : min(100, (int) (($points - $currentThreshold) / max(1, $nextThreshold - $currentThreshold) * 100));

        return [
            'number' => $currentLevel,
            'name' => $current['name'],
            'points' => $points,
            'current_threshold' => $currentThreshold,
            'next' => $next === null
                ? null
                : [
                    'name' => $next['name'],
                    'points' => $next['points'],
                    'remaining' => max(0, $next['points'] - $points),
                ],
            'progress' => $progress,
        ];
    }
}
