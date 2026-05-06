<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'HR Manajemen')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50" x-data="{ sidebarOpen: true, mobileOpen: false }">

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <!-- Mobile overlay -->
    <div x-show="mobileOpen"
         @click="mobileOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-30 bg-black/50 lg:hidden"
         style="display:none"></div>

    <!-- Sidebar -->
    <aside
        :class="sidebarOpen ? 'w-64' : 'w-16'"
        class="fixed top-0 left-0 h-full z-40 bg-gradient-to-b from-blue-900 to-blue-950 text-white flex flex-col transition-all duration-300 ease-in-out
               hidden lg:flex">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-5 border-b border-blue-800">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <span x-show="sidebarOpen" x-transition class="font-bold text-sm leading-tight whitespace-nowrap">
                HR Manajemen
            </span>
            <button @click="sidebarOpen = !sidebarOpen"
                    x-show="sidebarOpen"
                    class="ml-auto text-blue-300 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            <button @click="sidebarOpen = !sidebarOpen"
                    x-show="!sidebarOpen"
                    class="text-blue-300 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <!-- OLD NAV REMOVED
        <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-2">

            @php
            $menu = [
                ['label'=>'Dashboard',        'route'=>'dashboard',        'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['label'=>'Master Karyawan',  'route'=>'karyawan.index',   'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['label'=>'Absensi',          'route'=>'absensi.index',    'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label'=>'Cuti',             'route'=>'cuti.index',       'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['label'=>'Shift Kerja',      'route'=>'shift.index',      'icon'=>'M4 6h16M4 10h16M4 14h16M4 18h16'],
                ['label'=>'Lembur',           'route'=>'lembur.index',     'icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
                ['label'=>'Payroll',          'route'=>'payroll.index',    'icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ['label'=>'Penilaian Kinerja','route'=>'kinerja.index',    'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['label'=>'Rekrutmen',        'route'=>'rekrutmen.index',  'icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                ['label'=>'Manajemen User',   'route'=>'pengaturan.users.index', 'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ];

            $trainingIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
            $trainingActive = request()->routeIs('training.*');
            $badgeEksternal = \App\Models\TrainingEksternal::whereIn('status', ['menunggu_atasan','menunggu_hrd','menunggu_validasi'])->count();
            @endphp

            @foreach($menu as $item)
            @php $active = request()->routeIs($item['route']) @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                      {{ $active
                           ? 'bg-blue-500/30 text-white'
                           : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ $active ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                @if($active)
                <span x-show="sidebarOpen" class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                @endif
            </a>
            @endforeach

            {{-- Training: group dengan sub-menu --}}
            <div x-data="{ open: {{ $trainingActive ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                               {{ $trainingActive ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0 {{ $trainingActive ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $trainingIcon }}"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">Training</span>
                    @if($badgeEksternal > 0)
                    <span x-show="sidebarOpen" class="px-1.5 py-0.5 text-xs bg-orange-400 text-white rounded-full font-bold leading-none">
                        {{ $badgeEksternal }}
                    </span>
                    @endif
                    <svg x-show="sidebarOpen" class="w-3.5 h-3.5 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open && sidebarOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5">
                    @php
                        $subMenu = [
                            ['label'=>'IHT', 'route'=>'training.iht.index', 'match'=>'training.iht.*'],
                            ['label'=>'Eksternal', 'route'=>'training.eksternal.index', 'match'=>'training.eksternal.*'],
                            ['label'=>'Setting', 'route'=>'training.setting', 'match'=>'training.setting'],
                        ];
                    @endphp
                    @foreach($subMenu as $sub)
                    @php $subActive = request()->routeIs($sub['match']) @endphp
                    <a href="{{ route($sub['route']) }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition
                              {{ $subActive ? 'bg-blue-500/20 text-white' : 'text-blue-300 hover:bg-blue-800/40 hover:text-white' }}">
                        <span class="w-1 h-1 rounded-full bg-current opacity-60"></span>
                        {{ $sub['label'] }}
                        @if($sub['route'] === 'training.eksternal.index' && $badgeEksternal > 0)
                        <span class="ml-auto px-1.5 py-0.5 text-xs bg-orange-400 text-white rounded-full font-bold leading-none">
                            {{ $badgeEksternal }}
                        </span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

        </nav> -->
        <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-2">
@php
    $user         = auth()->user();
    $role         = $user->role ?? 'karyawan';
    $isHrdAdmin   = in_array($role, ['hrd', 'admin']);
    $isAtasanUp   = in_array($role, ['atasan', 'hrd', 'admin']);
    $isAdmin      = $role === 'admin';
    $trainingActive = request()->routeIs('training.*');
    $trainingIcon   = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';

    // ── Hitung badge per role (dibungkus try/catch agar error DB tidak crash sidebar) ──
    $badgeCuti      = 0;
    $badgeLembur    = 0;
    $badgeEksternal = 0;
    $badgeRekrutmen = 0;

    try {
        if ($role === 'atasan') {
            $nikBawahan     = \App\Models\AtasanPegawai::nikBawahan($user->id);
            $badgeCuti      = \App\Models\PengajuanCuti::where('status', 'Menunggu Atasan')
                                  ->whereIn('nik', $nikBawahan)->count();
            $pegIds         = \App\Models\Pegawai::whereIn('nik', $nikBawahan)->pluck('id');
            $badgeLembur    = \App\Models\Lembur::where('status', 'Menunggu Atasan')
                                  ->whereIn('pegawai_id', $pegIds)->count();
            $badgeEksternal = \App\Models\TrainingEksternal::where('status', 'menunggu_atasan')
                                  ->where('atasan_id', $user->id)->count();
        } elseif ($isHrdAdmin) {
            $badgeCuti      = \App\Models\PengajuanCuti::where('status', 'Menunggu HRD')->count();
            $badgeLembur    = \App\Models\Lembur::where('status', 'Menunggu HRD')->count();
            $badgeEksternal = \App\Models\TrainingEksternal::whereIn('status', ['menunggu_hrd', 'menunggu_validasi'])->count();
            // Rekrutmen ada di SIK — hanya pakai filter status tanpa tanggal_tutup
            $badgeRekrutmen = \App\Models\Rekrutmen::where('status', 'buka')->count();
        }
    } catch (\Throwable $e) {
        // Badge tidak tampil jika DB error — tidak crash halaman
    }

    // ── navLink helper (dengan badge opsional) ────────────────────────────────
    function navLink(string $label, string $route, string $icon, int $badge = 0): string {
        $active  = request()->routeIs($route);
        $base    = $active ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white';
        $svg     = $active ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200';
        $dot     = $active ? '<span x-show="sidebarOpen" class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span>' : '';
        $badgeHtml = $badge > 0
            ? "<span x-show=\"sidebarOpen\" class=\"ml-auto px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full font-bold leading-none\">{$badge}</span>"
            : $dot;
        $url = route($route);
        return <<<HTML
        <a href="{$url}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group {$base}">
            <svg class="w-5 h-5 flex-shrink-0 {$svg}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{$icon}"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1">{$label}</span>
            {$badgeHtml}
        </a>
        HTML;
    }
@endphp

{{-- ═══════════════════════════════ KARYAWAN ════════════════════════════════ --}}
@if($role === 'karyawan')
    {!! navLink('Portal Karyawan', 'ess.dashboard', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z') !!}
    {!! navLink('Cuti', 'cuti.index', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z') !!}
    {!! navLink('Lembur', 'lembur.index', 'M13 10V3L4 14h7v7l9-11h-7z') !!}
    {!! navLink('Training Eksternal', 'training.eksternal.index', $trainingIcon) !!}
@endif

{{-- ═══════════════════════════════ ATASAN ════════════════════════════════ --}}
@if($role === 'atasan')
    {!! navLink('Dashboard', 'dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') !!}
    {!! navLink('ESS (Portal Saya)', 'ess.dashboard', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z') !!}
    {!! navLink('Cuti', 'cuti.index', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', $badgeCuti) !!}
    {!! navLink('Lembur', 'lembur.index', 'M13 10V3L4 14h7v7l9-11h-7z', $badgeLembur) !!}
    {!! navLink('Training Eksternal', 'training.eksternal.index', $trainingIcon, $badgeEksternal) !!}
@endif

{{-- ═══════════════════════════════ HRD & ADMIN ════════════════════════════ --}}
@if($isHrdAdmin)
    {!! navLink('Dashboard', 'dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') !!}
    {!! navLink('Master Karyawan', 'karyawan.index', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z') !!}
    {!! navLink('Absensi', 'absensi.index', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z') !!}
    {!! navLink('Cuti', 'cuti.index', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', $badgeCuti) !!}
    {!! navLink('Shift Kerja', 'shift.index', 'M4 6h16M4 10h16M4 14h16M4 18h16') !!}
    {!! navLink('Lembur', 'lembur.index', 'M13 10V3L4 14h7v7l9-11h-7z', $badgeLembur) !!}
    {!! navLink('Payroll', 'payroll.index', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z') !!}
    {!! navLink('Penilaian Kinerja', 'kinerja.index', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z') !!}
    {!! navLink('Rekrutmen', 'rekrutmen.index', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', $badgeRekrutmen) !!}

    {{-- Training: dropdown IHT + Eksternal + Setting --}}
    <div x-data="{ open: {{ $trainingActive ? 'true' : 'false' }} }">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                       {{ $trainingActive ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $trainingActive ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $trainingIcon }}"/>
            </svg>
            <span x-show="sidebarOpen" class="flex-1 text-left">Training</span>
            @if($badgeEksternal > 0)
            <span x-show="sidebarOpen" class="px-1.5 py-0.5 text-xs bg-orange-400 text-white rounded-full font-bold">{{ $badgeEksternal }}</span>
            @endif
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 transition-transform" :class="open?'rotate-180':''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open && sidebarOpen" x-cloak class="ml-4 mt-1 space-y-0.5">
            @foreach([
                ['IHT',       'training.iht.index',      'training.iht.*'],
                ['Eksternal', 'training.eksternal.index', 'training.eksternal.*'],
                ['Setting',   'training.setting',         'training.setting'],
            ] as [$lbl, $rt, $match])
            @php $sa = request()->routeIs($match) @endphp
            <a href="{{ route($rt) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition
                      {{ $sa ? 'bg-blue-500/20 text-white' : 'text-blue-300 hover:bg-blue-800/40 hover:text-white' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-60"></span>{{ $lbl }}
                @if($rt === 'training.eksternal.index' && $badgeEksternal > 0)
                <span class="ml-auto px-1.5 py-0.5 bg-orange-400 text-white rounded-full text-xs font-bold">{{ $badgeEksternal }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    {{-- Pengaturan: Atasan (HRD & Admin) --}}
    @php
        $atasanBelumSet = \App\Models\Pegawai::aktif()->whereDoesntHave('atasanRecord')->count();
        $atasanActive   = request()->routeIs('pengaturan.atasan.*');
    @endphp
    <div class="pt-3 mt-3 border-t border-blue-800 space-y-0.5">
        <p x-show="sidebarOpen" class="px-3 mb-1 text-[10px] uppercase tracking-wider text-blue-400">Pengaturan</p>
        <a href="{{ route('pengaturan.atasan.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                  {{ $atasanActive ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $atasanActive ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="sidebarOpen" class="flex-1">Atasan Langsung</span>
            @if($atasanBelumSet > 0)
            <span x-show="sidebarOpen"
                  class="px-1.5 py-0.5 text-xs bg-orange-400 text-white rounded-full font-bold">
                {{ $atasanBelumSet }}
            </span>
            @endif
        </a>

        {{-- Admin only --}}
        @if($isAdmin)
        {!! navLink('Manajemen User', 'pengaturan.users.index', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z') !!}
        @endif
    </div>
@endif

        </nav>

        {{-- User info bottom --}}
        <div class="border-t border-blue-800 p-3" x-show="sidebarOpen">
            <div class="flex items-center gap-3 px-1">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold uppercase flex-shrink-0">
                    {{ substr(auth()->user()->nama ?? 'U', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->nama ?? 'User' }}</p>
                    <p class="text-xs text-blue-300 truncate">{{ auth()->user()->jabatan ?? 'Administrator' }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- MAIN CONTENT AREA --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-16'" class="flex flex-col min-h-screen transition-all duration-300">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center gap-4 px-4 lg:px-6 h-14">

                {{-- Mobile menu toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title --}}
                <div>
                    <h1 class="text-sm font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-400">@yield('page-subtitle', 'Selamat datang di HR Manajemen')</p>
                </div>

                <div class="ml-auto flex items-center gap-3">

                    {{-- Date --}}
                    <span class="hidden md:block text-xs text-gray-400">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>

                    {{-- Notification --}}
                    <button class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    {{-- User dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition text-sm">
                            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold uppercase">
                                {{ substr(auth()->user()->nama ?? 'U', 0, 1) }}
                            </div>
                            <span class="hidden md:block font-medium text-gray-700">{{ auth()->user()->nama ?? 'User' }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                             style="display:none">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-800 truncate">{{ auth()->user()->nama ?? '' }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profil Saya
                            </a>
                            <form method="POST" action="{{ route('logout') ?? '/logout' }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="py-3 px-6 border-t border-gray-100 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} HR Manajemen — By IT RSIA Respati; All rights reserved.
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
