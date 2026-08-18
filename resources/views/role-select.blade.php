<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karya Tantri Abadi — Pilih Role</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <img src="{{ asset('img/logo-karya-tantri-abadi.png') }}" alt="Logo" class="h-16 w-auto mx-auto mb-4">
            <h1 class="text-3xl font-bold text-white">Karya Tantri Abadi</h1>
            <p class="text-gray-400 mt-2">Sistem Koperasi Simpan Pinjam</p>
            <p class="text-gray-500 text-sm mt-1">Pilih peran untuk masuk</p>
        </div>

        <div class="space-y-3">
            @php
                $roles = [
                    'admin'    => ['Administrator', 'Kelola sistem, data anggota, pinjaman, laporan, backup', 'bg-green-600 hover:bg-green-500'],
                    'spv'      => ['Supervisor (SPV)', 'Setujui / tolak pengajuan pinjaman', 'bg-indigo-600 hover:bg-indigo-500'],
                    'kasir'    => ['Kasir', 'Cairkan pinjaman & catat tabungan', 'bg-amber-600 hover:bg-amber-500'],
                    'anggota'  => ['Anggota', 'Pantau pinjaman kelompok sendiri', 'bg-sky-600 hover:bg-sky-500'],
                    'petugas'  => ['Petugas Lapangan', 'Input data nasabah', 'bg-rose-600 hover:bg-rose-500'],
                ];
            @endphp

            @foreach ($roles as $key => [$label, $desc, $color])
                <a href="/{{ $key }}/login"
                   class="block w-full {{ $color }} text-white rounded-xl p-5 transition shadow-lg hover:shadow-xl">
                    <div class="text-lg font-semibold">{{ $label }}</div>
                    <div class="text-sm opacity-90 mt-1">{{ $desc }}</div>
                </a>
            @endforeach
        </div>

        <p class="text-center text-gray-600 text-xs mt-8">Koperasi Karya Tantri Abadi © {{ date('Y') }}</p>
    </div>
</body>
</html>
