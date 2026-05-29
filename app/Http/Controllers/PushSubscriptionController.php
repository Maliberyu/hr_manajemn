<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'        => 'required|url',
            'keys.p256dh'     => 'required|string',
            'keys.auth'       => 'required|string',
        ]);

        PushSubscription::saveForUser(auth()->id(), $request->all());

        return response()->json(['status' => 'subscribed']);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);
        PushSubscription::removeForUser(auth()->id(), $request->endpoint);
        return response()->json(['status' => 'unsubscribed']);
    }

    public function vapidKey()
    {
        return response()->json(['key' => config('app.vapid_public_key')]);
    }
}
