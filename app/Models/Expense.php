<?php

namespace App\Models;

use App\Enums\ExpensePaymentMethod;
use App\Enums\ExpensePaymentStatus;
use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Enums\RecurringFrequency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Expense extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    protected $fillable = [
        'expense_number',
        'event_id',
        'booking_id',
        'category_id',
        'expense_type',
        'title',
        'description',
        'amount',
        'currency',
        'tax_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'expense_date',
        'due_date',
        'paid_at',
        'vendor_name',
        'vendor_phone',
        'vendor_email',
        'attachments',
        'is_recurring',
        'recurring_frequency',
        'recurring_start_date',
        'recurring_end_date',
        'recurring_count',
        'parent_expense_id',
        'status',
        'notes',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $translatable = ['title', 'description'];

    protected $casts = [
        'amount' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'expense_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'recurring_start_date' => 'date',
        'recurring_end_date' => 'date',
        'recurring_count' => 'integer',
        'is_recurring' => 'boolean',
        'attachments' => 'array',
        'approved_at' => 'datetime',
        'expense_type' => ExpenseType::class,
        'payment_method' => ExpensePaymentMethod::class,
        'payment_status' => ExpensePaymentStatus::class,
        'status' => ExpenseStatus::class,
        'recurring_frequency' => RecurringFrequency::class,
    ];

    protected $attributes = [
        'currency' => 'OMR',
        'tax_amount' => 0.000,
        'payment_status' => 'paid',
        'status' => 'approved',
        'is_recurring' => false,
        'recurring_count' => 0,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Expense $expense): void {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateExpenseNumber();
            }

            if (auth()->check() && empty($expense->created_by)) {
                $expense->created_by = auth()->id();
            }
        });

        static::saving(function (Expense $expense): void {
            if (
                $expense->isDirty('payment_status') &&
                $expense->payment_status === ExpensePaymentStatus::Paid &&
                empty($expense->paid_at)
            ) {
                $expense->paid_at = now();
            }

            if (
                $expense->isDirty('status') &&
                $expense->status === ExpenseStatus::Approved &&
                empty($expense->approved_at)
            ) {
                $expense->approved_at = now();
                if (auth()->check()) {
                    $expense->approved_by = auth()->id();
                }
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'expense_type', 'title', 'amount', 'tax_amount', 'payment_method',
                'payment_status', 'expense_date', 'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function parentExpense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'parent_expense_id');
    }

    public function childExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'parent_expense_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForBooking(Builder $query, int $bookingId): Builder
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ExpenseStatus::Approved);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', ExpensePaymentStatus::Paid);
    }

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->whereIn('payment_status', [
            ExpensePaymentStatus::Pending,
            ExpensePaymentStatus::Partial,
        ]);
    }

    public function scopeOfType(Builder $query, ExpenseType $type): Builder
    {
        return $query->where('expense_type', $type);
    }

    public function scopeInDateRange(Builder $query, $startDate, $endDate = null): Builder
    {
        $query->where('expense_date', '>=', $startDate);

        if ($endDate) {
            $query->where('expense_date', '<=', $endDate);
        }

        return $query;
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring', true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 3) . ' ' . $this->currency;
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total_amount, 3) . ' ' . $this->currency;
    }

    public function getIsEditableAttribute(): bool
    {
        return $this->status->isEditable();
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->payment_status === ExpensePaymentStatus::Paid) {
            return false;
        }

        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    public static function generateExpenseNumber(): string
    {
        $year = date('Y');
        $prefix = 'EXP';

        $lastExpense = self::withTrashed()
            ->where('expense_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('expense_number', 'desc')
            ->first();

        $newNumber = $lastExpense
            ? ((int) substr($lastExpense->expense_number, -5)) + 1
            : 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $newNumber);
    }

    public function markAsPaid(?string $reference = null, ?ExpensePaymentMethod $method = null): bool
    {
        $this->payment_status = ExpensePaymentStatus::Paid;
        $this->paid_at = now();

        if ($reference) {
            $this->payment_reference = $reference;
        }

        if ($method) {
            $this->payment_method = $method;
        }

        return $this->save();
    }

    public function approve(?int $approvedBy = null): bool
    {
        if (!$this->status->canTransitionTo(ExpenseStatus::Approved)) {
            return false;
        }

        $this->status = ExpenseStatus::Approved;
        $this->approved_at = now();
        $this->approved_by = $approvedBy ?? auth()->id();

        return $this->save();
    }

    public function reject(string $reason): bool
    {
        if (!$this->status->canTransitionTo(ExpenseStatus::Rejected)) {
            return false;
        }

        $this->status = ExpenseStatus::Rejected;
        $this->rejection_reason = $reason;

        return $this->save();
    }

    /**
     * Net profitability of the linked event: ticket/service revenue minus approved expenses.
     *
     * @return array{revenue: float, expenses: float, profit: float, margin: float}|null
     */
    public function getEventProfitability(): ?array
    {
        if (!$this->event_id || !$this->event) {
            return null;
        }

        $revenue = (float) $this->event->bookings()
            ->where('status', 'confirmed')
            ->sum('total_price');

        $expenses = (float) self::where('event_id', $this->event_id)
            ->approved()
            ->sum('total_amount');

        $profit = $revenue - $expenses;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
            'margin' => round($margin, 2),
        ];
    }
}
