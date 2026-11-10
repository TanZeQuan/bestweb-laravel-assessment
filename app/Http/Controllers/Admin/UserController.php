<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteUsersRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->role === 'admin'
            && $request->user()?->status === 'active',
            403
        );

        $filters = $request->validate([
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $query = User::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $users = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function create(): View
    {
        abort_unless(
            auth()->user()?->role === 'admin'
            && auth()->user()?->status === 'active',
            403
        );

        return view('admin.create-user');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'admin'
            && $request->user()?->status === 'active',
            403
        );

        User::create($request->validated());

        return redirect()
            ->route('admin.users')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        abort_unless(
            auth()->user()?->role === 'admin'
            && auth()->user()?->status === 'active',
            403
        );

        return view('admin.edit-user', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'admin'
            && $request->user()?->status === 'active',
            403
        );

        $validated = $request->validated();

        // Prevent an admin from disabling their own account.
        if ($user->is($request->user()) && ($validated['status'] ?? $user->status) !== 'active') {
            return back()
                ->withErrors(['status' => 'You cannot deactivate your own account.'])
                ->withInput();
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'admin'
            && $request->user()?->status === 'active',
            403
        );

        // Prevent the admin from deleting their own account.
        abort_if($user->is($request->user()), 403);

        $user->delete();

        return redirect()
            ->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }

    public function bulkDestroy(BulkDeleteUsersRequest $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'admin'
            && $request->user()?->status === 'active',
            403
        );

        $ids = $request->validated('user_ids');

        // Prevent the admin from deleting their own account.
        abort_if(in_array($request->user()->id, $ids), 403);

        $deletedCount = User::whereIn('id', $ids)->delete();

        return redirect()
            ->route('admin.users')
            ->with('success', "{$deletedCount} users deleted successfully.");
    }

    public function export(Request $request): BinaryFileResponse
    {
        abort_unless(
            $request->user()?->role === 'admin'
            && $request->user()?->status === 'active',
            403
        );

        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
