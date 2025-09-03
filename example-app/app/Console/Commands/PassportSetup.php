<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Client;

class PassportSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Passport clients for API authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Laravel Passport...');

        // Create personal access client
        $personalClient = Client::where('name', 'Personal Access Client')->first();

        if (!$personalClient) {
            $personalClient = Client::create([
                'name' => 'Personal Access Client',
                'secret' => null,
                'redirect_uris' => json_encode(['http://localhost']),
                'grant_types' => json_encode(['personal_access']),
                'revoked' => false,
            ]);

            $this->info('Personal access client created successfully.');
            $this->info('Client ID: ' . $personalClient->id);
        } else {
            $this->info('Personal access client already exists.');
            $this->info('Client ID: ' . $personalClient->id);
        }

        // Create password grant client
        $passwordClient = Client::where('name', 'Password Grant Client')->first();

        if (!$passwordClient) {
            $passwordClient = Client::create([
                'name' => 'Password Grant Client',
                'secret' => \Illuminate\Support\Str::random(40),
                'redirect_uris' => json_encode(['http://localhost']),
                'grant_types' => json_encode(['password']),
                'revoked' => false,
            ]);

            $this->info('Password grant client created successfully.');
            $this->info('Client ID: ' . $passwordClient->id);
            $this->info('Client Secret: ' . $passwordClient->secret);
        } else {
            $this->info('Password grant client already exists.');
            $this->info('Client ID: ' . $passwordClient->id);
        }

        $this->info('Passport setup completed successfully!');

        return 0;
    }
}
