<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('app.vapid_subject', 'mailto:admin@example.com'),
                'publicKey'  => config('app.vapid_public_key'),
                'privateKey' => config('app.vapid_private_key'),
            ],
        ]);
        $this->webPush->setReuseVAPIDHeaders(true);
    }

    public function sendToUser(int $userId, string $title, string $body, string $link = '/'): void
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        if ($subscriptions->isEmpty()) return;

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'link'  => url($link),
            'icon'  => asset('images/iconhrm.png'),
            'badge' => asset('images/iconhrm.png'),
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
                'keys' => [
                    'p256dh' => $sub->public_key,
                    'auth'   => $sub->auth_token,
                ],
            ]);
            $this->webPush->queueNotification($subscription, $payload);
        }

        foreach ($this->webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // Hapus subscription yang sudah tidak valid (unsubscribed)
                if ($report->getResponse() && in_array($report->getResponse()->getStatusCode(), [404, 410])) {
                    PushSubscription::where('endpoint', $report->getRequest()->getUri()->__toString())->delete();
                }
            }
        }
    }
}
