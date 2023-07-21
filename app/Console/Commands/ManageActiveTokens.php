<?php

namespace App\Console\Commands;

use App\Models\AccessToken;
use Illuminate\Console\Command;

class ManageActiveTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manage-active-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage active tokens: Checks for expired tokens and sets them to inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get tokens that are expired and set them to inactive
        AccessToken::where('expires_at', '<', now())->update(['is_active' => false, 'updated_at' => now()]);

        $this->info('Active tokens managed successfully');

        return 0;
    }
}
