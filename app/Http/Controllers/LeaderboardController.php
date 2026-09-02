<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class LeaderboardController extends Controller
{
    public function index(): View
    {
        $users = User::withCount('badges')
            ->orderByDesc('points')
            ->limit(20)
            ->get();

        return view('pages.leaderboard', compact('users'));
    }
}
