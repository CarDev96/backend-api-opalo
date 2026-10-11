<?php

namespace App\Jobs;

use App\Mail\UserWelcomeMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendUserWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new UserWelcomeMail($this->user, $this->plainPassword)
        );
    }
}
