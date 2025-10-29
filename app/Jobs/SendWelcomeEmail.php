<?php

namespace App\Jobs;

use App\Mail\WelcomeClientMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(new WelcomeClientMail($this->user));
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
            throw $e; // Re-throw to mark job as failed
        }
    }
}
