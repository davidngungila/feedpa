<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailCredential;

class EmailCredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if email credentials already exist
        $existing = EmailCredential::where('email_address', 'feedtan15@gmail.com')->first();
        
        if ($existing) {
            // Update existing credentials
            $existing->update([
                'password' => 'xgbs yqgn kmjy buqn',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'encryption' => 'tls',
                'from_name' => 'FeedTan Community Microfinance Group',
                'from_address' => 'feedtan15@gmail.com',
                'mailer' => 'smtp',
                'is_active' => true
            ]);
            
            $this->command->info('✅ Email credentials updated successfully!');
        } else {
            // Create new email credentials
            EmailCredential::create([
                'email_address' => 'feedtan15@gmail.com',
                'password' => 'xgbs yqgn kmjy buqn',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'encryption' => 'tls',
                'from_name' => 'FeedTan Community Microfinance Group',
                'from_address' => 'feedtan15@gmail.com',
                'mailer' => 'smtp',
                'is_active' => true
            ]);
            
            $this->command->info('✅ Email credentials created successfully!');
        }
    }
}
