<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $payments;

    public function __construct(array $payments)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return collect($this->payments);
    }

    public function headings(): array
    {
        return [
            'Order Reference',
            'Transaction ID',
            'Status',
            'Amount',
            'Currency',
            'Phone/Email',
            'Description',
            'Payment Method',
            'Created At',
            'Updated At'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment['orderReference'] ?? 'N/A',
            $payment['transactionId'] ?? 'N/A',
            $payment['status'] ?? 'N/A',
            number_format($payment['amount'] ?? 0, 2),
            $payment['currency'] ?? 'TZS',
            ($payment['phone'] ?? $payment['email']) ?? 'N/A',
            $payment['description'] ?? 'N/A',
            $payment['paymentMethod'] ?? 'N/A',
            isset($payment['createdAt']) ? \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m-d H:i:s') : 'N/A',
            isset($payment['updatedAt']) ? \Carbon\Carbon::parse($payment['updatedAt'])->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:J1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ]
            ]
        ];
    }
}
