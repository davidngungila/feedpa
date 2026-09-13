<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SmsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $messages;

    public function __construct($messages)
    {
        $this->messages = $messages;
    }

    public function collection()
    {
        return $this->messages;
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            'Device',
            'Provider',
            'Sender',
            'Message Body',
            'Amount (TZS)',
            'Reference / Txn ID',
            'Counterparty',
            'Counterparty Name',
            'Balance',
            'Type',
            'Recorded',
            'Recorded By',
            'Comment',
            'Sync Status',
            'Processing Status',
        ];
    }

    public function map($sms): array
    {
        $txn = $sms->smsTransaction;
        return [
            $sms->sms_timestamp ? $sms->sms_timestamp->format('Y-m-d H:i:s') : ($sms->created_at ? $sms->created_at->format('Y-m-d H:i:s') : 'N/A'),
            $sms->device ? $sms->device->device_code.' — '.$sms->device->name : 'N/A',
            $sms->provider ? $sms->provider->name.' ('.$sms->provider->code.')' : ($sms->parsed_data['provider_code'] ?? 'N/A'),
            $sms->sender ?? 'N/A',
            $sms->body ?? 'N/A',
            $txn && $txn->amount ? number_format($txn->amount, 2) : '—',
            $txn && $txn->reference ? $txn->reference : '—',
            $txn && $txn->counterparty ? $txn->counterparty : '—',
            $txn && $txn->counterparty_name ? $txn->counterparty_name : '—',
            $txn && $txn->balance ? number_format($txn->balance, 2) : '—',
            $txn ? $txn->transaction_type : '—',
            $sms->is_recorded ? 'Recorded' : 'Not Recorded',
            $sms->recordedBy ? $sms->recordedBy->name : '—',
            $sms->admin_comment ?? '—',
            $sms->sync_status ?? '—',
            $sms->processing_status ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            "A1:P1" => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D1FAE5']
                ]
            ]
        ];
    }
}
