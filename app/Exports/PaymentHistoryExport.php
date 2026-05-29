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
    protected $columns;

    public function __construct(array $payments, array $columns = [])
    {
        $this->payments = $payments;
        $this->columns = $columns;
    }

    public function collection()
    {
        return collect($this->payments);
    }

    public function headings(): array
    {
        if (empty($this->columns)) {
            return [
                'Order Reference',
                'Transaction ID',
                'Status',
                'Amount',
                'Currency',
                'Payer Name',
                'Phone',
                'Email',
                'Description',
                'Payment Method',
                'Created At',
                'Updated At'
            ];
        }

        $headings = [];
        foreach ($this->columns as $col) {
            $headings[] = ucwords(str_replace('_', ' ', $col));
        }
        return $headings;
    }

    public function map($payment): array
    {
        if (empty($this->columns)) {
            return [
                $payment['order_reference'] ?? 'N/A',
                $payment['transaction_id'] ?? 'N/A',
                $payment['status'] ?? 'N/A',
                number_format($payment['amount'] ?? 0, 2),
                $payment['currency'] ?? 'TZS',
                $payment['payer_name'] ?? 'N/A',
                $payment['phone'] ?? 'N/A',
                $payment['email'] ?? 'N/A',
                $payment['description'] ?? 'N/A',
                $payment['payment_method'] ?? 'N/A',
                isset($payment['created_at']) ? \Carbon\Carbon::parse($payment['created_at'])->format('Y-m-d H:i:s') : 'N/A',
                isset($payment['updated_at']) ? \Carbon\Carbon::parse($payment['updated_at'])->format('Y-m-d H:i:s') : 'N/A'
            ];
        }

        $row = [];
        foreach ($this->columns as $col) {
            $value = $payment[$col] ?? 'N/A';
            if ($col === 'amount') {
                $value = number_format($value, 2);
            } elseif (in_array($col, ['created_at', 'updated_at', 'sms_sent_at'])) {
                $value = $value && $value !== 'N/A' ? \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s') : 'N/A';
            }
            $row[] = $value;
        }
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->getExcelColumnName(count($this->headings()));
        return [
            1 => ['font' => ['bold' => true]],
            "A1:{$lastCol}1" => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ]
            ]
        ];
    }

    private function getExcelColumnName($index)
    {
        $letters = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letters = chr(65 + $remainder) . $letters;
            $index = intval(($index - $remainder) / 26);
        }
        return $letters;
    }
}
