<?php

namespace App\Http\Controllers;

use App\Models\HrNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifikasi = HrNotification::forUser(auth()->id())
            ->latest()
            ->paginate(20);

        HrNotification::forUser(auth()->id())->unread()->update(['read_at' => now()]);

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function baca(HrNotification $notifikasi)
    {
        abort_if($notifikasi->user_id !== auth()->id(), 403);
        $notifikasi->update(['read_at' => now()]);

        return redirect($notifikasi->link ?: route('dashboard'));
    }

    public function bacaSemua()
    {
        HrNotification::forUser(auth()->id())->unread()->update(['read_at' => now()]);
        return back();
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => HrNotification::forUser(auth()->id())->unread()->count()
        ]);
    }
}
