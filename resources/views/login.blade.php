<!-- strona logowania -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login</title>
</head>
<body class="mx-auto py-6 px-4 bg-black">
    <div class="min-h-screen bg-black flex flex-col justify-center py-12 sm:px-6 lg:px-8 px-6">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="mx-auto h-12 w-auto text-6xl text-center">🔑</div>
            <h2 class="mt-6 text-center text-3xl leading-9 font-extrabold text-white">
                Sign in to your account
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-gray-900 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium leading-5 text-gray-200">Email address</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input id="email" name="email" placeholder="user@example.com" type="email" required class="appearance-none block w-full px-3 py-2 border border-gray-700 bg-black text-white rounded-md placeholder-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm sm:leading-5" value="{{ old('email') }}">
                        </div>
                        @error('email')
                            <span class="text-red-400 text-sm">{{ $message }}</span> <!-- komunikat o błędzie emaila -->
                        @enderror
                    </div>

                    <div class="mt-6">
                        <label for="password" class="block text-sm font-medium leading-5 text-gray-200">Password</label>
                        <div class="mt-1 rounded-md shadow-sm">
                            <input id="password" name="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-700 bg-black text-white rounded-md placeholder-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-500 transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                        </div>
                        @error('password')
                            <span class="text-red-400 text-sm">{{ $message }}</span> <!-- komunikat o błędzie hasła -->
                        @enderror
                    </div>

                    <a href="{{ route('password.request') }}"
                        class="font-medium text-blue-400 hover:text-blue-300 focus:outline-none focus:underline transition ease-in-out duration-150">
                        Forgot your password? <!-- link do resetowania hasła -->
                    </a>

                    <div class="mt-6">
                        <span class="block w-full rounded-md shadow-sm">
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-black bg-blue-400 hover:bg-blue-300 focus:outline-none focus:border-blue-700 focus:shadow-outline-indigo active:bg-blue-700 transition duration-150 ease-in-out">
                                Sign in <!-- przycisk logowania -->
                            </button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>