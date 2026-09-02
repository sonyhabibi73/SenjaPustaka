<?php

use App\Models\Category;

test('iconName memetakan slug ke nama ikon Lucide', function () {
    $cases = [
        'fiksi' => 'book-open',
        'fantasi' => 'sparkles',
        'romantis' => 'heart',
        'misteri' => 'search',
        'sejarah' => 'landmark',
        'teknologi' => 'cpu',
        'sains' => 'atom',
        'bisnis' => 'trending-up',
        'komik' => 'palette',
        'self-help' => 'sprout',
        'puisi' => 'feather',
        'biografi' => 'user',
        'novel' => 'book-open',
    ];

    foreach ($cases as $slug => $icon) {
        $category = new Category(['slug' => $slug, 'emoji' => '📚']);

        expect($category->iconName())->toBe($icon);
    }
});

test('iconName memakai nama ikon yang tersimpan di kolom emoji', function () {
    $category = new Category(['slug' => 'fiksi', 'emoji' => 'heart']);

    expect($category->iconName())->toBe('heart');
});
