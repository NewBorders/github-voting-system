<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Feature Voting')</title>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dark-primary': '#06085a',
                        'dark-secondary': '#0a0c7a',
                        'dark-border': '#1a1c9a',
                        'accent': {
                            DEFAULT: '#9333ea',
                            light: '#a855f7',
                            dark: '#7c3aed',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --color-dark-primary: #06085a;
            --color-dark-secondary: #0a0c7a;
            --color-dark-border: #1a1c9a;
            --color-accent: #9333ea;
        }
        
        body {
            background-color: var(--color-dark-primary);
        }
        
        /* Globale Dark Theme Styles */
        .card {
            background-color: var(--color-dark-secondary);
            border: 1px solid var(--color-dark-border);
        }
        
        input:not([type="checkbox"]):not([type="radio"]),
        textarea,
        select {
            background-color: var(--color-dark-primary);
            border-color: var(--color-dark-border);
            color: #e5e7eb;
        }
        
        input:focus:not([type="checkbox"]):not([type="radio"]),
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);
        }
        
        .btn-primary {
            background-color: var(--color-accent);
            border-color: #7c3aed;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #7c3aed;
        }
        
        .btn-secondary {
            background-color: var(--color-dark-secondary);
            border-color: var(--color-dark-border);
            color: #d1d5db;
        }
        
        .btn-secondary:hover {
            background-color: #3d3494;
        }
        
        /* Status Badge Styles */
        .badge {
            border: 1px solid;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-submitted {
            background-color: #1f2937;
            color: #e5e7eb;
            border-color: #4b5563;
        }
        
        .badge-planned {
            background-color: #1e3a8a;
            color: #bfdbfe;
            border-color: #3b82f6;
        }
        
        .badge-in_progress {
            background-color: #854d0e;
            color: #fef3c7;
            border-color: #eab308;
        }
        
        .badge-done {
            background-color: #4c1d95;
            color: #e9d5ff;
            border-color: #a855f7;
        }
        
        .badge-accepted {
            background-color: #14532d;
            color: #bbf7d0;
            border-color: #22c55e;
        }
        
        .badge-active {
            background-color: #14532d;
            color: #bbf7d0;
            border-color: #22c55e;
        }
        
        .badge-inactive {
            background-color: #1f2937;
            color: #d1d5db;
            border-color: #4b5563;
        }
    </style>
</head>
<body class="bg-dark-primary text-gray-200">
    <nav class="bg-dark-secondary shadow-lg border-b border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="{{ route('voting.index') }}" class="flex items-center px-2 py-2 text-xl font-bold text-accent-light hover:text-accent">
                        🗳️ Feature Voting
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('voting.index') }}" class="text-gray-300 hover:text-accent-light px-3 py-2 rounded-md text-sm font-medium">
                        Vote
                    </a>
                    <a href="{{ route('admin.index') }}" class="text-gray-300 hover:text-accent-light px-3 py-2 rounded-md text-sm font-medium">
                        Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="badge-accepted px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <script>
        // Store client ID in localStorage
        function getClientId() {
            let clientId = localStorage.getItem('voting_client_id');
            if (!clientId) {
                clientId = 'client-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now();
                localStorage.setItem('voting_client_id', clientId);
            }
            return clientId;
        }

        // Add client_id to all HTMX requests
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.parameters.client_id = getClientId();
        });

        // Add CSRF token to all requests
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        });
    </script>
</body>
</html>
