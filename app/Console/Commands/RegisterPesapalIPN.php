<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PesapalService;

class RegisterPesapalIPN extends Command
{
    protected $signature = 'pesapal:register-ipn';
    protected $description = 'Register IPN URL with Pesapal';

    public function handle()
    {
        $this->info('Registering IPN URL with Pesapal...');

        $pesapalService = new PesapalService();
        $result = $pesapalService->registerIPN();

        if ($result['success']) {
            $this->info('✓ IPN registered successfully!');
            $this->info('IPN ID: ' . ($result['ipn_id'] ?? 'N/A'));
            $this->info('');
            $this->warn('IMPORTANT: Add this to your .env file:');
            $this->line('PESAPAL_IPN_ID=' . ($result['ipn_id'] ?? ''));
            $this->info('');
            $this->info('Then update your config/pesapal.php to include:');
            $this->line("'ipn_id' => env('PESAPAL_IPN_ID'),");
            
            return Command::SUCCESS;
        }

        $this->error('✗ Failed to register IPN');
        $this->error($result['message'] ?? 'Unknown error');
        
        return Command::FAILURE;
    }
}