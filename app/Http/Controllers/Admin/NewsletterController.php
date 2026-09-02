<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request): View
    {
        $query = NewsletterSubscriber::latest();

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where('email', 'like', "%{$q}%");
        }

        $subscribers = $query->paginate(15)->withQueryString();

        return view('admin.subscribers', compact('subscribers'));
    }

    public function update(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->subscribed = $request->boolean('subscribed');
        $subscriber->save();

        return back()->with('success', 'Status langganan diperbarui.');
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber dihapus.');
    }
}
