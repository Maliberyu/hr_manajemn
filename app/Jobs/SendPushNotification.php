<?php

namespace App\Jobs;

use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 30;

    public function __construct(
        public readonly int    $userId,
        public readonly string $title,
        public readonly string $body,
        public readonly string $link = '/',
    ) {}

    public function handle(PushNotificationService $service): void
    {
        $service->sendToUser($this->userId, $this->title, $this->body, $this->link);
    }
}
