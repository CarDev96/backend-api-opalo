<?php

namespace App\Jobs;

use App\Mail\UserPasswordResetMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendUserPasswordResetJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new UserPasswordResetMail($this->user, $this->plainPassword));
    }
}
