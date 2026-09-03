<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Karya Tantri Abadi — Login</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('img/logo-karya-tantri-abadi.png') }}" alt="Logo" class="h-16 w-auto mx-auto mb-4">
            <h1 class="text-3xl font-bold text-gray-900">Karya Tantri Abadi</h1>
            <p class="text-gray-600 mt-2">Sistem Koperasi Simpan Pinjam</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Masuk ke akun Anda</h2>

            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username"
                        class="block w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 focus:border-green-600 focus:ring-2 focus:ring-green-600/20 outline-none"
                        placeholder="nama@contoh.test">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="block w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 focus:border-green-600 focus:ring-2 focus:ring-green-600/20 outline-none"
                        placeholder="Masukkan password">
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                        class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-600">
                    <label for="remember" class="ml-2 text-sm text-gray-700">Ingat saya</label>
                </div>

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg px-4 py-2.5 transition shadow-sm">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-xs mt-8">Koperasi Karya Tantri Abadi © {{ date('Y') }}</p>
    </div>
</body>
</html>
