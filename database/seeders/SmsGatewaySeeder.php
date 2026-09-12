<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsProvider;
use App\Models\SmsLocation;

class SmsGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['name' => 'M-Pesa', 'code' => 'MPESA', 'sender_ids' => 'MPesa,M-Pesa,MPESA', 'detection_keywords' => json_encode(['mpesa','m-pesa'])],
            ['name' => 'Airtel Money', 'code' => 'AIRTEL', 'sender_ids' => 'AirtelMoney,Airtel', 'detection_keywords' => json_encode(['airtel'])],
            ['name' => 'Mixx by Yas', 'code' => 'MIXX', 'sender_ids' => 'Mixx,TigoPesa,Yas,YasTigo', 'detection_keywords' => json_encode(['mixx','yas','tigo'])],
            ['name' => 'HaloPesa', 'code' => 'HALOPESA', 'sender_ids' => 'HaloPesa,Halotel', 'detection_keywords' => json_encode(['halopesa','halotel'])],
            ['name' => 'Generic Bank', 'code' => 'BANK', 'sender_ids' => '', 'detection_keywords' => json_encode(['bank','crdb','nmb','nbc'])],
        ];
        foreach ($providers as $p) {
            SmsProvider::updateOrCreate(['code' => $p['code']], $p);
        }

        $locations = [
            ['name' => 'Moshi', 'code' => 'MOSHI', 'description' => 'Kilimanjaro - Moshi'],
            ['name' => 'Tabora', 'code' => 'TABORA', 'description' => 'Tabora HQ for accountants'],
            ['name' => 'Arusha', 'code' => 'ARUSHA', 'description' => 'Arusha'],
            ['name' => 'Dar es Salaam', 'code' => 'DAR', 'description' => 'Dar'],
            ['name' => 'Dodoma', 'code' => 'DODOMA', 'description' => 'Dodoma'],
        ];
        foreach ($locations as $l) {
            SmsLocation::updateOrCreate(['code' => $l['code']], $l);
        }
    }
}
