<?php

namespace App\Jobs;

use App\Mail\ClientWelcomeMail;
use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendClientWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public Client $client,
        public User $user,
        public string $plainPassword
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new ClientWelcomeMail($this->client, $this->user, $this->plainPassword)
        );
    }
}
