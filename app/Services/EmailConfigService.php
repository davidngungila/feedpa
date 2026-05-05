<?php

namespace App\Services;

use App\Models\EmailCredential;
use Illuminate\Support\Facades\Config;

class EmailConfigService
{
    /**
     * Get email configuration from database
     */
    public function getEmailConfig(): array
    {
        $credential = EmailCredential::where('is_active', true)->first();
        
        if (!$credential) {
            return $this->getDefaultConfig();
        }
        
        return [
            'mailer' => $credential->mailer ?? 'smtp',
            'host' => $credential->smtp_host,
            'port' => $credential->smtp_port,
            'username' => $credential->email_address,
            'password' => $credential->password,
            'encryption' => $credential->encryption,
            'from_address' => $credential->from_address ?? $credential->email_address,
            'from_name' => $credential->from_name
        ];
    }
    
    /**
     * Save email configuration to database
     */
    public function saveEmailConfig(array $config): EmailCredential
    {
        // Deactivate existing credentials
        EmailCredential::where('is_active', true)->update(['is_active' => false]);
        
        // Create new credentials
        return EmailCredential::create([
            'email_address' => $config['username'],
            'password' => $config['password'],
            'smtp_host' => $config['host'],
            'smtp_port' => $config['port'],
            'encryption' => $config['encryption'],
            'from_name' => $config['from_name'],
            'from_address' => $config['from_address'] ?? $config['username'],
            'mailer' => $config['mailer'] ?? 'smtp',
            'is_active' => true
        ]);
    }
    
    /**
     * Configure Laravel mail system with database settings
     */
    public function configureMail(): void
    {
        $config = $this->getEmailConfig();
        
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $config['host'],
            'port' => $config['port'],
            'encryption' => $config['encryption'],
            'username' => $config['username'],
            'password' => $config['password'],
            'timeout' => null,
            'auth_mode' => null,
        ]);
        
        Config::set('mail.from.address', $config['from_address']);
        Config::set('mail.from.name', $config['from_name']);
        Config::set('mail.default', $config['mailer']);
    }
    
    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'mailer' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_address' => '',
            'from_name' => 'FeedTan Community Microfinance Group'
        ];
    }
    
    /**
     * Test email configuration
     */
    public function testEmailConfig(): bool
    {
        try {
            $this->configureMail();
            
            // Try to send a test email
            \Mail::raw('Test email from database configuration', function ($message) {
                $message->to('feedtan15@gmail.com')
                        ->subject('Email Config Test')
                        ->from('feedtan15@gmail.com', 'FeedTan Community Microfinance Group');
            });
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Email config test failed: ' . $e->getMessage());
            return false;
        }
    }
}
