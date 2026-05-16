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
<body class="h-full bg-gray-50" x-data="{ sidebarOpen: true, mobileOpen: false, featureModal: false }">

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
        :class="sidebarOpen ? 'w-64' : 'w-64 lg:w-16'"
        :style="mobileOpen ? 'display:flex' : ''"
        class="fixed top-0 left-0 h-full z-40 bg-gradient-to-b from-blue-900 to-blue-950 text-white flex-col transition-all duration-300 ease-in-out hidden lg:flex">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-5 border-b border-blue-800">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <span class="font-bold text-sm leading-tight whitespace-nowrap flex-1"
                  :class="(!sidebarOpen && !mobileOpen) ? 'lg:hidden' : ''">
                HR Manajemen
            </span>
            {{-- Tutup mobile sidebar --}}
            <button @click="mobileOpen = false"
                    class="lg:hidden text-blue-300 hover:text-white transition ml-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            {{-- Toggle collapse desktop --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:block text-blue-300 hover:text-white transition ml-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
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
        <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-2"
             @click.capture="if($event.target.closest('a[href]')) mobileOpen = false">
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

    // ── navLink helper (dengan badge & feature flag opsional) ────────────────
    function navLink(string $label, string $route, string $icon, int $badge = 0, ?string $feature = null): string {
        $disabled  = $feature && !config("features.{$feature}", true);
        $active    = !$disabled && request()->routeIs($route);
        $base      = $active ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white';
        $svg       = $active ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200';

        if ($disabled) {
            return <<<HTML
            <button @click="featureModal = true"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group {$base} opacity-60 cursor-pointer">
                <svg class="w-5 h-5 flex-shrink-0 {$svg}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{$icon}"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">{$label}</span>
                <span x-show="sidebarOpen" class="text-xs opacity-60">!</span>
            </button>
            HTML;
        }

        $dot       = $active ? '<span x-show="sidebarOpen" class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span>' : '';
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
    @php $cutiIjinActive = request()->routeIs('cuti.*') || request()->routeIs('ijin.*'); @endphp
    <div x-data="{ open: {{ $cutiIjinActive ? 'true' : 'false' }} }">
        @if(!config('features.cuti', true))
        <button @click="featureModal = true"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition text-blue-200 hover:bg-blue-800/50 opacity-60">
            <svg class="w-5 h-5 flex-shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap flex-1 text-left">Cuti & Ijin</span>
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 opacity-50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </button>
        @else
        <button @click="open = !open"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ $cutiIjinActive ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $cutiIjinActive ? 'text-blue-300' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap flex-1 text-left">Cuti & Ijin</span>
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open && sidebarOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5">
            @foreach([['Pengajuan Cuti','cuti.index','cuti.*'],['Ijin Sakit','ijin.index','ijin.*'],['Ijin Terlambat','ijin.index',''],['Ijin Pulang Duluan','ijin.index','']] as $i => [$lbl,$rt,$match])
            @php
                $params = match($i) { 0 => [], 1 => ['jenis'=>'sakit'], 2 => ['jenis'=>'terlambat'], 3 => ['jenis'=>'pulang_duluan'] };
                $sa = $i === 0 ? request()->routeIs('cuti.*') : (request()->routeIs('ijin.*') && request()->route('jenis') === ($params['jenis'] ?? ''));
            @endphp
            <a href="{{ route($rt, $params) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition
                      {{ $sa ? 'bg-blue-500/20 text-white' : 'text-blue-300 hover:bg-blue-800/40 hover:text-white' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-60 flex-shrink-0"></span>{{ $lbl }}
            </a>
            @endforeach
        </div>
        @endif
    </div>
    {!! navLink('Lembur', 'lembur.index', 'M13 10V3L4 14h7v7l9-11h-7z', 0, 'lembur') !!}
    {!! navLink('Training Eksternal', 'training.eksternal.index', $trainingIcon, 0, 'training') !!}
    @php
        $slipKaryawan = 0;
        try { $slipKaryawan = \App\Models\SlipGaji::where('pegawai_id', auth()->user()->pegawai?->id)->final()->count(); } catch(\Throwable $e) {}
    @endphp
    @php
        $urlSlipKaryawan = route('ess.dashboard', ['tab' => 'payroll']);
        $activeSlipK     = request()->routeIs('ess.dashboard') && request('tab') === 'payroll';
        $base   = $activeSlipK ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white';
        $svg    = $activeSlipK ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200';
        $badge  = $slipKaryawan > 0 ? "<span x-show=\"sidebarOpen\" class=\"ml-auto px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full font-bold leading-none\">{$slipKaryawan}</span>" : '';
    @endphp
    <a href="{{ $urlSlipKaryawan }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group {{ $base }}">
        <svg class="w-5 h-5 flex-shrink-0 {{ $svg }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1">Slip Gaji</span>
        {!! $badge !!}
    </a>
@endif

{{-- ═══════════════════════════════ ATASAN ════════════════════════════════ --}}
@if($role === 'atasan')
    {!! navLink('Dashboard', 'dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') !!}
    {!! navLink('ESS (Portal Saya)', 'ess.dashboard', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z') !!}
    @php $cutiIjinActiveA = request()->routeIs('cuti.*') || request()->routeIs('ijin.*'); @endphp
    <div x-data="{ open: {{ $cutiIjinActiveA ? 'true' : 'false' }} }">
        <button @click="{{ !config('features.cuti', true) ? 'featureModal = true' : 'open = !open' }}"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ $cutiIjinActiveA ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }} {{ !config('features.cuti',true) ? 'opacity-60' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $cutiIjinActiveA ? 'text-blue-300' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap flex-1 text-left">Cuti & Ijin</span>
            @if($badgeCuti > 0)
            <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full font-bold leading-none">{{ $badgeCuti }}</span>
            @elseif(config('features.cuti', true))
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            @else
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 opacity-50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            @endif
        </button>
        @if(config('features.cuti', true))
        <div x-show="open && sidebarOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5">
            @foreach([['Pengajuan Cuti','cuti.index'],['Ijin Sakit','ijin.index'],['Ijin Terlambat','ijin.index'],['Ijin Pulang Duluan','ijin.index']] as $i => [$lbl,$rt])
            @php
                $params = match($i) { 0 => [], 1 => ['jenis'=>'sakit'], 2 => ['jenis'=>'terlambat'], 3 => ['jenis'=>'pulang_duluan'] };
                $sa = $i === 0 ? request()->routeIs('cuti.*') : (request()->routeIs('ijin.*') && request()->route('jenis') === ($params['jenis'] ?? ''));
            @endphp
            <a href="{{ route($rt, $params) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition
                      {{ $sa ? 'bg-blue-500/20 text-white' : 'text-blue-300 hover:bg-blue-800/40 hover:text-white' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-60 flex-shrink-0"></span>{{ $lbl }}
            </a>
            @endforeach
        </div>
        @endif
    </div>
    {!! navLink('Lembur', 'lembur.index', 'M13 10V3L4 14h7v7l9-11h-7z', $badgeLembur, 'lembur') !!}
    {!! navLink('Training Eksternal', 'training.eksternal.index', $trainingIcon, $badgeEksternal, 'training') !!}
    @php
        $slipAtasan = 0;
        try { $slipAtasan = \App\Models\SlipGaji::where('pegawai_id', auth()->user()->pegawai?->id)->final()->count(); } catch(\Throwable $e) {}
    @endphp
    @php
        $urlSlipAtasan = route('ess.dashboard', ['tab' => 'payroll']);
        $activeSlipA   = request()->routeIs('ess.dashboard') && request('tab') === 'payroll';
        $baseA  = $activeSlipA ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white';
        $svgA   = $activeSlipA ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200';
        $badgeA = $slipAtasan > 0 ? "<span x-show=\"sidebarOpen\" class=\"ml-auto px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full font-bold leading-none\">{$slipAtasan}</span>" : '';
    @endphp
    <a href="{{ $urlSlipAtasan }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group {{ $baseA }}">
        <svg class="w-5 h-5 flex-shrink-0 {{ $svgA }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1">Slip Gaji Saya</span>
        {!! $badgeA !!}
    </a>
@endif

{{-- ═══════════════════════════════ HRD & ADMIN ════════════════════════════ --}}
@if($isHrdAdmin)
    {!! navLink('Dashboard', 'dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') !!}
    {!! navLink('Master Karyawan', 'karyawan.index', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z') !!}
    {!! navLink('Absensi', 'absensi.index', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z') !!}
    @php $cutiIjinActiveH = request()->routeIs('cuti.*') || request()->routeIs('ijin.*'); @endphp
    <div x-data="{ open: {{ $cutiIjinActiveH ? 'true' : 'false' }} }">
        <button @click="{{ !config('features.cuti', true) ? 'featureModal = true' : 'open = !open' }}"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ $cutiIjinActiveH ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }} {{ !config('features.cuti',true) ? 'opacity-60' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $cutiIjinActiveH ? 'text-blue-300' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap flex-1 text-left">Cuti & Ijin</span>
            @if($badgeCuti > 0)
            <span x-show="sidebarOpen" class="px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full font-bold leading-none">{{ $badgeCuti }}</span>
            @elseif(config('features.cuti', true))
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            @else
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 opacity-50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            @endif
        </button>
        @if(config('features.cuti', true))
        <div x-show="open && sidebarOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5">
            @foreach([['Pengajuan Cuti','cuti.index'],['Ijin Sakit','ijin.index'],['Ijin Terlambat','ijin.index'],['Ijin Pulang Duluan','ijin.index']] as $i => [$lbl,$rt])
            @php
                $params = match($i) { 0 => [], 1 => ['jenis'=>'sakit'], 2 => ['jenis'=>'terlambat'], 3 => ['jenis'=>'pulang_duluan'] };
                $sa = $i === 0 ? request()->routeIs('cuti.*') : (request()->routeIs('ijin.*') && request()->route('jenis') === ($params['jenis'] ?? ''));
            @endphp
            <a href="{{ route($rt, $params) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition
                      {{ $sa ? 'bg-blue-500/20 text-white' : 'text-blue-300 hover:bg-blue-800/40 hover:text-white' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-60 flex-shrink-0"></span>{{ $lbl }}
            </a>
            @endforeach
        </div>
        @endif
    </div>
    {!! navLink('Shift Kerja', 'shift.index', 'M4 6h16M4 10h16M4 14h16M4 18h16', 0, 'shift') !!}
    {!! navLink('Lembur', 'lembur.index', 'M13 10V3L4 14h7v7l9-11h-7z', $badgeLembur, 'lembur') !!}
    {!! navLink('Payroll', 'payroll.index', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 0, 'payroll') !!}
    {!! navLink('Penilaian Kinerja', 'kinerja.index', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 0, 'kinerja') !!}

    {{-- KPI: dropdown Dashboard + Target + Rekap --}}
    @php
        $kpiDisabled = !config('features.kpi', true);
        $kpiActive   = request()->routeIs('kpi.*');
        $kpiIcon     = 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z';
    @endphp
    <div x-data="{ open: {{ !$kpiDisabled && $kpiActive ? 'true' : 'false' }} }">
        <button @click="{{ $kpiDisabled ? 'featureModal = true' : 'open = !open' }}"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                       {{ $kpiActive ? 'bg-blue-500/30 text-white' : 'text-blue-200 hover:bg-blue-800/50 hover:text-white' }}
                       {{ $kpiDisabled ? 'opacity-60' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $kpiActive ? 'text-blue-300' : 'text-blue-400 group-hover:text-blue-200' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $kpiIcon }}"/>
            </svg>
            <span x-show="sidebarOpen" class="flex-1 text-left">KPI</span>
            @if(!$kpiDisabled)
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            @else
            <svg x-show="sidebarOpen" class="w-3.5 h-3.5 opacity-50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            @endif
        </button>
        @if(!$kpiDisabled)
        <div x-show="open && sidebarOpen" x-cloak class="ml-4 mt-0.5 space-y-0.5">
            @foreach([
                ['Dashboard KPI', 'kpi.index',  'kpi.index'],
                ['Target KPI',   'kpi.target', 'kpi.target'],
                ['Rekap KPI',    'kpi.rekap',  'kpi.rekap'],
            ] as [$lbl, $rt, $match])
            @php $sa = request()->routeIs($match); @endphp
            <a href="{{ route($rt) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition
                      {{ $sa ? 'bg-blue-500/20 text-white' : 'text-blue-300 hover:bg-blue-800/40 hover:text-white' }}">
                <span class="w-1 h-1 rounded-full bg-current opacity-60 flex-shrink-0"></span>{{ $lbl }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {!! navLink('Rekrutmen', 'rekrutmen.index', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', $badgeRekrutmen, 'rekrutmen') !!}

    {{-- Training: dropdown IHT + Eksternal + Setting --}}
    @php $trainingDisabled = !config('features.training', true); @endphp
    <div x-data="{ open: {{ !$trainingDisabled && $trainingActive ? 'true' : 'false' }} }">
        <button @click="{{ $trainingDisabled ? 'featureModal = true' : 'open = !open' }}"
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

    {{-- Portal Karyawan (HRD/Admin yang punya link pegawai) --}}
    @if(auth()->user()->pegawai)
    <div class="pt-3 mt-3 border-t border-blue-800">
        <p x-show="sidebarOpen" class="px-3 mb-1 text-[10px] uppercase tracking-wider text-blue-400">Portal Saya</p>
        {!! navLink('Portal Karyawan', 'ess.dashboard', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z') !!}
    </div>
    @endif

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
                    @php
                        $unreadCount = 0;
                        try {
                            $unreadCount = \App\Models\HrNotification::forUser(auth()->id())->unread()->count();
                            $recentNotif = \App\Models\HrNotification::forUser(auth()->id())->latest()->limit(5)->get();
                        } catch (\Throwable) { $recentNotif = collect(); }
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                            @endif
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden"
                             style="display:none">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">Notifikasi</p>
                                @if($unreadCount > 0)
                                <form method="POST" action="{{ parse_url(route('notifikasi.baca.semua'), PHP_URL_PATH) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:underline">Baca semua</button>
                                </form>
                                @endif
                            </div>
                            @forelse($recentNotif as $n)
                            <a href="{{ parse_url(route('notifikasi.baca', $n), PHP_URL_PATH) }}"
                               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0 {{ $n->read_at ? '' : 'bg-blue-50/40' }}">
                                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center mt-0.5
                                    {{ str_contains($n->type,'approved') ? 'bg-green-100' : (str_contains($n->type,'rejected') ? 'bg-red-100' : 'bg-blue-100') }}">
                                    @if(str_contains($n->type,'approved'))
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @elseif(str_contains($n->type,'rejected'))
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @else
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 flex items-center gap-1">
                                        {{ $n->title }}
                                        @if(!$n->read_at)<span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>@endif
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">{{ $n->message }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                                </div>
                            </a>
                            @empty
                            <div class="py-8 text-center text-gray-400 text-xs">Tidak ada notifikasi</div>
                            @endforelse
                            <a href="{{ route('notifikasi.index') }}"
                               class="block px-4 py-2.5 text-xs text-center text-blue-600 hover:bg-gray-50 transition font-medium">
                                Lihat semua notifikasi →
                            </a>
                        </div>
                    </div>

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
                            <a href="{{ route('profil.show') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
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
            &copy; {{ date('Y') }} HR Manajemen — Respati; All rights reserved.
        </footer>

    </div>

    @stack('scripts')

    {{-- ── Modal: Feature Disabled ─────────────────────────────────────────── --}}
    <!-- Modal -->
<div x-show="featureModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
     style="display:none"
     @click.self="featureModal = false">

    <div x-show="featureModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="bg-white rounded-2xl p-6 w-[320px] shadow-2xl text-center">

        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-orange-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 class="text-base font-bold text-gray-800 mb-2">Fitur Belum Tersedia</h3>

        <p class="text-sm text-gray-500 leading-relaxed mb-5">
            Fitur ini masih dalam tahap pengembangan.
        </p>

        <button @click="featureModal = false"
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition">
            OK, Mengerti
        </button>
    </div>
</div>

    {{-- ── Toast: Feature Disabled (fallback akses langsung via URL) ──────── --}}
    @if(session('feature_disabled'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 w-full max-w-sm px-4">
        <div class="bg-gray-900 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-orange-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-sm leading-relaxed flex-1">{{ session('feature_disabled') }}</p>
            <button @click="show = false" class="text-gray-400 hover:text-white transition flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

</body>
</html>
