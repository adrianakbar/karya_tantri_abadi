<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="fi min-h-screen">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Filament Styles -->
    @filamentStyles
    
    <style>
        :root {
            --primary-50: #f0fdf4;
            --primary-100: #dcfce7;
            --primary-200: #bbf7d0;
            --primary-300: #86efac;
            --primary-400: #4ade80;
            --primary-500: #22c55e;
            --primary-600: #16a34a;
            --primary-700: #15803d;
            --primary-800: #166534;
            --primary-900: #14532d;
            --primary-950: #052e16;
        }
        
        .fi-simple-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .fi-simple-page {
            width: 100%;
            max-width: 28rem;
        }
        
        .fi-simple-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .fi-logo {
            margin-bottom: 1rem;
        }
        
        .fi-simple-form {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            border: 1px solid rgb(229 231 235);
            padding: 2rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .fi-simple-form {
                background: rgb(17 24 39);
                border-color: rgb(55 65 81);
            }
        }
    </style>
</head>

<body class="fi-body fi-panel-app min-h-screen bg-gray-50 font-sans antialiased dark:bg-gray-950">
    <div class="fi-simple-layout flex min-h-screen flex-col items-center">
        <div class="fi-simple-main-ctn flex w-full flex-1 flex-col items-center justify-center">
            <main class="fi-simple-main">
                <div class="fi-simple-page">
                    <header class="fi-simple-header">
                        <div class="fi-logo">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <img src="{{ asset('img/logo-karya-tantri-abadi.png') }}" alt="Logo" class="h-12 w-auto">
                                <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                    Karya Tantri Abadi
                                </span>
                            </div>
                        </div>
                        <h1 class="fi-simple-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                            Login
                        </h1>
                    </header>

                    <div class="fi-simple-form">
                        @if ($errors->any())
                            <div class="fi-section mb-6">
                                <div class="rounded-lg bg-danger-50 p-4 dark:bg-danger-950/50">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                                Terjadi kesalahan saat login
                                            </h3>
                                            <div class="mt-2 text-sm text-danger-700 dark:text-danger-300">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="fi-form space-y-6">
                            @csrf
                            
                            <div class="fi-fo-field-wrp">
                                <div class="grid gap-y-2">
                                    <div class="flex items-center gap-x-3 justify-between">
                                        <label for="email" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                Email address
                                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                            </span>
                                        </label>
                                    </div>
                                    
                                    <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-2 focus-within:ring-primary-600 dark:focus-within:ring-primary-500">
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            value="{{ old('email') }}"
                                            required
                                            autofocus
                                            autocomplete="username"
                                            class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3 @error('email') ring-danger-600 dark:ring-danger-500 @enderror"
                                            placeholder="Masukkan alamat email Anda"
                                        >
                                    </div>
                                    
                                    @error('email')
                                        <div class="text-sm text-danger-600 dark:text-danger-400 mt-2">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="fi-fo-field-wrp">
                                <div class="grid gap-y-2">
                                    <div class="flex items-center gap-x-3 justify-between">
                                        <label for="password" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                Password
                                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                            </span>
                                        </label>
                                    </div>
                                    
                                    <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-2 focus-within:ring-primary-600 dark:focus-within:ring-primary-500">
                                        <input
                                            id="password"
                                            name="password"
                                            type="password"
                                            required
                                            autocomplete="current-password"
                                            class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3 @error('password') ring-danger-600 dark:ring-danger-500 @enderror"
                                            placeholder="Masukkan password Anda"
                                        >
                                    </div>
                                    
                                    @error('password')
                                        <div class="text-sm text-danger-600 dark:text-danger-400 mt-2">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="fi-fo-field-wrp">
                                <div class="flex items-center">
                                    <input
                                        id="remember"
                                        name="remember"
                                        type="checkbox"
                                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700"
                                        {{ old('remember') ? 'checked' : '' }}
                                    >
                                    <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                        Remember me
                                    </label>
                                </div>
                            </div>

                            <div class="fi-form-actions">
                                <div class="fi-ac-action-wrp">
                                    <button
                                        type="submit"
                                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action w-full"
                                        style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                    >
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin fi-btn-icon transition duration-75 h-5 w-5 text-white" wire:loading="" wire:target="authenticate" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                                        </svg>
                                        
                                        <span class="fi-btn-label">
                                            Login
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @filamentScripts
</body>
</html>
