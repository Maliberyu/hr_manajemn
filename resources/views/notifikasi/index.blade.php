@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Semua pemberitahuan masuk')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $notifikasi->total() }} notifikasi</p>
        <form method="POST" action="{{ parse_url(route('notifikasi.baca.semua'), PHP_URL_PATH) }}">
            @csrf
            <button type="submit" class="text-xs text-blue-600 hover:underline">Tandai semua dibaca</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @forelse($notifikasi as $n)
        <a href="{{ parse_url(route('notifikasi.baca', $n), PHP_URL_PATH) }}"
           class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 transition border-b border-gray-50 last:border-0
                  {{ $n->read_at ? '' : 'bg-blue-50/40' }}">
            <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
                {{ match(true) {
                    str_contains($n->type,'approved')  => 'bg-green-100',
                    str_contains($n->type,'rejected')  => 'bg-red-100',
                    str_contains($n->type,'submitted') => 'bg-blue-100',
                    default                            => 'bg-gray-100',
                } }}">
                @if(str_contains($n->type,'approved'))
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @elseif(str_contains($n->type,'rejected'))
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                @else
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    {{ $n->title }}
                    @if(!$n->read_at)
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $n->message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
            </div>
        </a>
        @empty
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm">Tidak ada notifikasi</p>
        </div>
        @endforelse
    </div>

    <div>{{ $notifikasi->links() }}</div>

</div>
@endsection
