<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class UpdateOfflineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-offline-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $offlineThreshold = Carbon::now()->subMinutes(1);

        User::where('is_online', true)
            ->where('last_seen', '<', $offlineThreshold)
            ->update(['is_online' => false]);

        $this->info('Offline users updated successfully.');
    }
}
