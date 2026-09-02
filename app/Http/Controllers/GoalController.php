<?php

namespace App\Http\Controllers;

use App\Models\ReadingGoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2020,2035'],
            'target_books' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'target_pages' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        ReadingGoal::updateOrCreate(
            ['user_id' => auth()->id(), 'year' => $data['year']],
            [
                'target_books' => $data['target_books'] ?? 0,
                'target_pages' => $data['target_pages'] ?? 0,
            ]
        );

        return back()->with('success', 'Target membaca diperbarui! 🎯');
    }
}
