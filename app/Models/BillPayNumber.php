<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class BillPayNumber extends Model
{
    use HasFactory;

    protected $table = 'billpay_numbers';

    protected $fillable = [
        'bill_pay_number',
        'bill_description',
        'bill_amount',
        'bill_currency',
        'bill_payment_mode',
        'bill_status',
        'bill_type',
        'customer_name',
        'customer_email',
        'customer_phone',
        'bill_reference',
        'notes',
        'created_by',
        'last_payment_at',
        'total_paid',
    ];

    protected $casts = [
        'bill_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'last_payment_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter by status
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status && $status !== 'all') {
            return $query->where('bill_status', $status);
        }
        return $query;
    }

    /**
     * Scope to search by number or description
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search) {
            return $query->where(function (Builder $q) use ($search) {
                $q->where('bill_pay_number', 'like', "%{$search}%")
                  ->orWhere('bill_description', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Scope to filter by bill type
     */
    public function scopeType(Builder $query, ?string $type): Builder
    {
        if ($type) {
            return $query->where('bill_type', $type);
        }
        return $query;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->bill_amount, 2) . ' ' . $this->bill_currency;
    }

    /**
     * Get formatted total paid
     */
    public function getFormattedTotalPaidAttribute(): string
    {
        return number_format($this->total_paid, 2) . ' ' . $this->bill_currency;
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->bill_amount - $this->total_paid);
    }

    /**
     * Get formatted remaining amount
     */
    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 2) . ' ' . $this->bill_currency;
    }

    /**
     * Check if bill is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->total_paid >= $this->bill_amount;
    }

    /**
     * Check if bill has partial payment
     */
    public function hasPartialPayment(): bool
    {
        return $this->total_paid > 0 && !$this->isFullyPaid();
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusColorAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'success';
        } elseif ($this->hasPartialPayment()) {
            return 'warning';
        } else {
            return 'secondary';
        }
    }

    /**
     * Get payment status text
     */
    public function getPaymentStatusTextAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'Paid';
        } elseif ($this->hasPartialPayment()) {
            return 'Partial';
        } else {
            return 'Unpaid';
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->bill_status) {
            'ACTIVE' => 'success',
            'INACTIVE' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get type badge color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->bill_type) {
            'order' => 'info',
            'customer' => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Create from ClickPesa API response
     */
    public static function createFromApiResponse(array $apiData, array $additionalData = []): self
    {
        // Debug logging
        Log::info('BillPay createFromApiResponse', [
            'api_data' => $apiData,
            'additional_data' => $additionalData,
            'bill_amount_from_api' => $apiData['billAmount'] ?? 'NOT_FOUND',
            'bill_amount_from_additional' => $additionalData['bill_amount'] ?? 'NOT_FOUND'
        ]);

        return static::create([
            'bill_pay_number' => $apiData['billPayNumber'] ?? null,
            'bill_description' => $apiData['billDescription'] ?? $additionalData['bill_description'] ?? 'BillPay Control Number',
            'bill_amount' => $apiData['billAmount'] ?? $additionalData['bill_amount'] ?? null,
            'bill_payment_mode' => $apiData['billPaymentMode'] ?? 'ALLOW_PARTIAL_AND_OVER_PAYMENT',
            'bill_status' => 'ACTIVE',
            'bill_type' => $additionalData['bill_type'] ?? 'order',
            'customer_name' => $apiData['billCustomerName'] ?? $additionalData['customer_name'] ?? null,
            'customer_email' => $additionalData['customer_email'] ?? null,
            'customer_phone' => $additionalData['customer_phone'] ?? null,
            'bill_reference' => $apiData['billReference'] ?? $additionalData['bill_reference'] ?? null,
            'notes' => $additionalData['notes'] ?? null,
            'created_by' => $additionalData['created_by'] ?? null,
        ]);
    }

    /**
     * Update payment information
     */
    public function updatePayment(float $amount): void
    {
        $this->total_paid += $amount;
        $this->last_payment_at = now();
        $this->save();
    }

    /**
     * Get customer display name
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->bill_type === 'customer' && $this->customer_name) {
            return $this->customer_name;
        }
        
        if ($this->customer_name) {
            return $this->customer_name;
        }
        
        if ($this->customer_email) {
            return $this->customer_email;
        }
        
        if ($this->customer_phone) {
            return $this->customer_phone;
        }
        
        return 'N/A';
    }
}
