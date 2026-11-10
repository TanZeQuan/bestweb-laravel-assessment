<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - BestWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f5f7fb;
        }

        .page-card {
            border: none;
            border-radius: 12px;
        }

        .table th {
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="bg-white border-bottom">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">BestWeb Admin</h5>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <main class="container py-4">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">User Management</h3>
            <p class="text-muted mb-0">
                Manage and view registered users.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a
                href="{{ route('admin.users.export') }}"
                class="btn btn-outline-success"
            >
                Export Excel
            </a>

            <a
                href="{{ route('admin.users.create') }}"
                class="btn btn-primary"
            >
                + Add User
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

   
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Validation failed:</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


        <!-- Status Filter -->
        <div class="card page-card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label">
                                Filter by Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-select"
                            >
                                <option value="">All Users</option>
                                <option
                                    value="active"
                                    @selected(request('status') === 'active')
                                >
                                    Active
                                </option>
                                <option
                                    value="inactive"
                                    @selected(request('status') === 'inactive')
                                >
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-primary">
                                Apply Filter
                            </button>
                        </div>

                        <div class="col-12 col-md-auto">
                            <a
                                href="{{ route('admin.users') }}"
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- User List -->
        <form
            id="bulk-delete-form"
            action="{{ route('admin.users.bulk-delete') }}"
            method="POST"
            onsubmit="return confirm('Are you sure you want to delete the selected users?')"
        >
            @csrf
        </form>
        <div class="card page-card shadow-sm">
            <div class="card-body p-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 border-bottom">
                <div>
                    <h6 class="fw-bold mb-1">Users</h6>

                    <span class="text-muted small">
                        Total: {{ $users->total() }}
                    </span>
                </div>

                <button
                    type="submit"
                    form="bulk-delete-form"
                    id="bulk-delete-btn"
                    class="btn btn-outline-danger btn-sm"
                    disabled
                >
                    Delete Selected
                </button>
            </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">
                                    <input
                                        type="checkbox"
                                        id="select-all"
                                        class="form-check-input"
                                        aria-label="Select all users on this page"
                                    >
                                </th>
                                <th class="ps-3">ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td class="ps-3">
                                        @if ($user->id !== auth()->id())
                                            <input
                                                type="checkbox"
                                                name="user_ids[]"
                                                value="{{ $user->id }}"
                                                form="bulk-delete-form"
                                                class="form-check-input user-checkbox"
                                                aria-label="Select {{ $user->name }}"
                                            >
                                        @endif
                                    </td>

                                    <td class="ps-3">
                                        {{ $user->id }}
                                    </td>


                                    <td class="fw-medium">
                                        {{ $user->name }}
                                    </td>

                                    <td>{{ $user->email }}</td>

                                    <td>{{ $user->phone_number }}</td>

                                    <td>
                                        <span class="badge {{ $user->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $user->created_at?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a
                                                href="{{ route('admin.users.edit', $user) }}"
                                                class="btn btn-outline-primary btn-sm"
                                            >
                                                Edit
                                            </a>

                                            @if ($user->id !== auth()->id())
                                                <form
                                                    action="{{ route('admin.users.destroy', $user) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?')"
                                                    class="m-0"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                        <span class="text-muted small">
                            Showing {{ $users->firstItem() ?? 0 }}
                            to {{ $users->lastItem() ?? 0 }}
                            of {{ $users->total() }} users
                        </span>

                        @if ($users->hasPages())
                            {{ $users->links('pagination::bootstrap-5') }}
                        @endif

                    </div>
                </div>

            </div>
        </div>

    </main>

</body>

<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const deleteButton = document.getElementById('bulk-delete-btn');

    function updateSelection() {
        const selectedCount = document.querySelectorAll(
            '.user-checkbox:checked'
        ).length;

        deleteButton.disabled = selectedCount === 0;

        selectAll.checked =
            checkboxes.length > 0 &&
            selectedCount === checkboxes.length;

        selectAll.indeterminate =
            selectedCount > 0 &&
            selectedCount < checkboxes.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });

        updateSelection();
    });

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSelection);
    });

    updateSelection();
</script>

</html>
