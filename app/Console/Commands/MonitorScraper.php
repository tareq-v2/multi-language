<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MonitorScraper extends Command
{
    protected $signature = 'scraper:monitor';
    protected $description = 'Monitor background scraping processes';

    public function handle()
    {
        $files = Storage::files('scraper_pids');

        foreach ($files as $file) {
            $pid = Storage::get($file);

            // Windows implementation
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'windows') {
                $command = "tasklist /FI \"PID eq $pid\"";
                $output = shell_exec($command);

                if (!str_contains($output, $pid)) {
                    Storage::delete($file);
                    $this->info("Process $pid completed");
                } else {
                    $this->info("Process $pid still running");
                }
            }
            // Linux implementation
            else {
                $command = "ps -p $pid";
                $output = shell_exec($command);

                if (strpos($output, $pid) === false) {
                    Storage::delete($file);
                    $this->info("Process $pid completed");
                } else {
                    $this->info("Process $pid still running");
                }
            }
        }
        return 0;
    }
}
