<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - BestWeb</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f5f7fb;
        }

        .form-card {
            max-width: 720px;
            margin: 0 auto;
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>

    <header class="bg-white border-bottom">
        <div class="container py-3">
            <h5 class="mb-0 fw-bold">BestWeb Admin</h5>
        </div>
    </header>

    <main class="container py-4">
        <div class="form-card mb-4">
            <a href="{{ route('admin.users') }}" class="text-decoration-none">
                &larr; Back to Users
            </a>

            <h3 class="fw-bold mt-3 mb-1">Edit User</h3>
            <p class="text-muted">
                Update user information.
            </p>
        </div>

        <div class="card form-card shadow-sm">
            <div class="card-body p-4">
                <form
                    action="{{ route('admin.users.update', $user) }}"
                    method="POST"
                >
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            required
                        >

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone_number" class="form-label">
                            Phone Number
                        </label>

                        <input
                            type="tel"
                            id="phone_number"
                            name="phone_number"
                            class="form-control @error('phone_number') is-invalid @enderror"
                            value="{{ old('phone_number', $user->phone_number) }}"
                            required
                        >

                        @error('phone_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >
                            <option
                                value="active"
                                @selected(old('status', $user->status) === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(old('status', $user->status) === 'inactive')
                            >
                                Inactive
                            </option>
                        </select>

                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <a
                            href="{{ route('admin.users') }}"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
