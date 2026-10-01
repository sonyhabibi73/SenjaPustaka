<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::withCount(['progress', 'reviews']);

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Only admins can change admin status
        if (! auth()->user()->is_admin) {
            abort(403);
        }

        $data = $request->validate([
            'is_admin' => ['nullable', 'boolean'],
        ]);

        if ($user->id === auth()->id() && $request->has('is_admin') && ! $request->boolean('is_admin')) {
            return back()->with('error', 'Tidak bisa mencabut peran admin dari akun sendiri.');
        }

        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        // Log admin role changes for audit trail
        Log::info('Admin role changed', [
            'changed_by' => auth()->id(),
            'target_user' => $user->id,
            'new_is_admin' => $user->is_admin,
            'ip' => $request->ip(),
        ]);

        return back()->with('success', 'Peran pengguna diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }
}
