<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Feature Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #1d1858;
        }
    </style>
</head>
<body class="bg-dark-primary">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                    🗳️ Admin Login
                </h2>
                <p class="mt-2 text-center text-sm text-gray-300">
                    Enter your admin token to access the dashboard
                </p>
            </div>
            
            @if(session('error'))
                <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            
            <form class="mt-8 space-y-6" action="{{ route('admin.login.post') }}" method="POST">
                @csrf
                <div>
                    <label for="token" class="sr-only">Admin Token</label>
                    <input id="token" 
                           name="token" 
                           type="password" 
                           required
                           class="appearance-none rounded-lg relative block w-full px-3 py-3 rounded-md">
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 rounded-md btn-primary font-medium">
                        Sign in
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('voting.index') }}" class="text-sm text-accent-light hover:text-accent">
                        ← Back to voting
                    </a>
                </div>
            </form>
            
            <div class="mt-6 text-center text-xs text-gray-500">
                <p>The admin token is set in your <code class="bg-gray-100 px-2 py-1 rounded">.env</code> file as <code class="bg-gray-100 px-2 py-1 rounded">ADMIN_API_TOKEN</code></p>
            </div>
        </div>
    </div>
</body>
</html>
