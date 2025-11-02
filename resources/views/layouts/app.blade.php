<!DOCTYPE html>
@php
    $themePreference = request()->cookie('theme', 'light');
    $isDarkMode = $themePreference === 'dark';
@endphp
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toilet Tycoon')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }

        body.dark-mode {
            background-color: #121212;
            color: #f8f9fa;
        }

        body.dark-mode a {
            color: #9ecbff;
        }

        body.dark-mode a:hover {
            color: #cfe3ff;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        body.dark-mode .card {
            background-color: #1f1f1f;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .card:hover {
            transform: translateY(-5px);
        }

        body.dark-mode .alert {
            background-color: #2c2c2c;
            color: #f8f9fa;
            border-color: #3a3a3a;
        }

        body.dark-mode .btn-outline-secondary {
            color: #f8f9fa;
            border-color: #6c757d;
        }

        body.dark-mode .btn-outline-secondary:hover {
            color: #121212;
            background-color: #f8f9fa;
        }

        body.dark-mode .btn-close {
            filter: invert(1);
        }
    </style>

    @stack('styles')
</head>
<body class="{{ $isDarkMode ? 'dark-mode' : '' }}" data-bs-theme="{{ $isDarkMode ? 'dark' : 'light' }}">

    <div class="container py-3 d-flex justify-content-end">
        <form method="POST" action="{{ route('theme.toggle') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                <i class="bi {{ $isDarkMode ? 'bi-sun-fill' : 'bi-moon-stars' }}"></i>
                <span>{{ $isDarkMode ? 'Light Mode' : 'Dark Mode' }}</span>
            </button>
        </form>
    </div>


    <!-- Flash Messages -->
    @if(session('success'))
        <div class="container">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Axios for AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // Setup CSRF token for all Axios requests
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        // Helper function to update balance display
        function updateBalance(newBalance) {
            const balanceEl = document.getElementById('user-balance');
            if (balanceEl) {
                balanceEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newBalance);

                // Animation effect
                balanceEl.parentElement.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    balanceEl.parentElement.style.transform = 'scale(1)';
                }, 200);
            }
        }

        // Helper function to show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'position-fixed bottom-0 end-0 p-3';
                container.style.zIndex = '11';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            document.getElementById('toast-container').appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
