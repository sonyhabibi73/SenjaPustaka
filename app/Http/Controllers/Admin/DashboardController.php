<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\NewsletterSubscriber;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'buku' => Book::count(),
            'user' => User::count(),
            'review' => Review::count(),
            'subscriber' => NewsletterSubscriber::where('subscribed', true)->count(),
        ];

        $topBooks = Book::with('author')->orderByDesc('views')->limit(5)->get();
        $recentUsers = User::latest()->limit(5)->get();
        $recentReviews = Review::with(['user', 'book'])->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'topBooks', 'recentUsers', 'recentReviews'));
    }
}
