<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ProcessQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-custom {--tries=3} {--timeout=90} {--sleep=3} {--max-jobs=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queue jobs with custom configuration for production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tries = $this->option('tries');
        $timeout = $this->option('timeout');
        $sleep = $this->option('sleep');
        $maxJobs = $this->option('max-jobs');

        $this->info("Starting queue worker with:");
        $this->info("- Tries: {$tries}");
        $this->info("- Timeout: {$timeout}s");
        $this->info("- Sleep: {$sleep}s");
        $this->info("- Max jobs: {$maxJobs}");

        // Use Process facade to run the queue:work command
        $process = Process::forever()
            ->run([
                'php',
                'artisan',
                'queue:work',
                '--tries=' . $tries,
                '--timeout=' . $timeout,
                '--sleep=' . $sleep,
                '--max-jobs=' . $maxJobs,
                '--verbose'
            ]);

        return $process->exitCode();
    }
}
