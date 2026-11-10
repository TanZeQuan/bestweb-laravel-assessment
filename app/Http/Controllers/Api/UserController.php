<?php

namespace App\Http\Controllers\Api;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteUsersRequest;
use App\Http\Requests\ListUsersRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    public function index(ListUsersRequest $request)
    {
        $filters = $request->validated();

        $query = User::query()
            ->select(['id', 'name', 'status', 'created_at']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->only([
                'id',
                'name',
                'email',
                'phone_number',
                'status',
            ]),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return response()->json([
            'data' => $user->only([
                'id',
                'name',
                'email',
                'phone_number',
                'status',
                'created_at',
            ]),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->only([
                'id',
                'name',
                'email',
                'phone_number',
                'status',
            ]),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        // Prevent admin from deleting their own account
        abort_if(
            $request->user()->id === $user->id,
            403,
            'You cannot delete your own account.'
        );

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function bulkDestroy(BulkDeleteUsersRequest $request): JsonResponse
    {
        $userIds = array_map('intval', $request->validated()['user_ids']);

        // Prevent admin from deleting their own account
        if (in_array((int) $request->user()->id, $userIds, true)) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $deletedCount = User::whereIn('id', $userIds)->delete();

        return response()->json([
            'message' => 'Users deleted successfully',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
