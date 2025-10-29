<?php

namespace App\Jobs;

use App\Mail\NewAccountConfirmationMail;
use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAccountConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $compte;

    /**
     * Create a new job instance.
     */
    public function __construct(Compte $compte)
    {
        $this->compte = $compte;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->compte->client->user->email)->send(new NewAccountConfirmationMail($this->compte));
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            \Log::error('Failed to send account confirmation email: ' . $e->getMessage());
            throw $e; // Re-throw to mark job as failed
        }
    }
}
