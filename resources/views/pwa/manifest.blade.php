{
    "name": "HR Manajemen",
    "short_name": "HR Mgmt",
    "description": "Sistem Informasi HR Manajemen",
    "start_url": "{{ url('/dashboard') }}",
    "scope": "{{ url('/') }}/",
    "display": "standalone",
    "orientation": "portrait",
    "background_color": "#1e3a8a",
    "theme_color": "#1d4ed8",
    "icons": [
        {
            "src": "{{ asset('images/iconhrm.png') }}",
            "sizes": "192x192",
            "type": "image/png",
            "purpose": "any maskable"
        },
        {
            "src": "{{ asset('images/iconhrm.png') }}",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "any maskable"
        }
    ]
}
