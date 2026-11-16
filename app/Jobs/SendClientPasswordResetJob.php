<?php

namespace App\Jobs;

use App\Mail\ClientPasswordResetMail;
use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendClientPasswordResetJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Client $client,
        public User $user,
        public string $plainPassword
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new ClientPasswordResetMail($this->client, $this->user, $this->plainPassword));
    }
}
