<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Feature Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    🗳️ Admin Login
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your admin token to access the dashboard
                </p>
            </div>
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
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
                           class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Admin Token">
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Sign in
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('voting.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
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
