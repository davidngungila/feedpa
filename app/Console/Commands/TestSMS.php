<?php

namespace App\Console\Commands;

use App\Services\MessagingServiceAPI;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Exception;

#[Signature('app:test-sms')]
#[Description('Test SMS messaging service')]
class TestSMS extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing SMS Messaging Service...');
        
        try {
            $messaging = new MessagingServiceAPI();
            
            // Test basic SMS
            $this->info('Sending test SMS to 0622239304...');
            $result = $messaging->sendSMS('0622239304', 'Test message from FEEDTAN system at ' . date('Y-m-d H:i:s'));
            
            $this->info('SMS Result:');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            
            // Test bill notification
            $this->info('Testing bill notification...');
            $billData = [
                'reference' => 'FEEDTAN' . time(),
                'description' => 'Test Bill',
                'amount' => 25000,
                'currency' => 'TZS'
            ];
            $billResult = $messaging->sendBillNotification('0622239304', $billData);
            
            $this->info('Bill Notification Result:');
            $this->line(json_encode($billResult, JSON_PRETTY_PRINT));
            
            $this->info('SMS tests completed successfully!');
            
        } catch (Exception $e) {
            $this->error('SMS test failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
