<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IspExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'description',
        'amount',
        'expense_date',
        'reference_no',
        'added_by',
        'linked_user_id',
        'linked_reseller_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    /** Human-readable category labels */
    public static array $categories = [
        'item_purchase'     => 'Item Purchase',
        'raw_bill'          => 'Raw Bill',
        'employee_salary'   => 'Employee Salary',
        'reseller_payout'   => 'Reseller Commission Payout',
        'miscellaneous'     => 'Miscellaneous',
    ];

    /** Category badge colours (Bootstrap) */
    public static array $categoryColors = [
        'item_purchase'     => 'primary',
        'raw_bill'          => 'warning',
        'employee_salary'   => 'info',
        'reseller_payout'   => 'purple',
        'miscellaneous'     => 'secondary',
    ];

    public static function generateReferenceNo(): string
    {
        $latest = static::whereNotNull('reference_no')->latest('id')->first();
        if ($latest && preg_match('/EXP-(\d+)/', $latest->reference_no, $matches)) {
            $nextNum = str_pad((int) $matches[1] + 1, 4, '0', STR_PAD_LEFT);
            return 'EXP-' . $nextNum;
        }

        return 'EXP-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::$categories[$this->category] ?? ucfirst($this->category);
    }

    public function getCategoryColorAttribute(): string
    {
        return self::$categoryColors[$this->category] ?? 'secondary';
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /** The employee/user this salary was paid to */
    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    /** The reseller this commission payout was made to */
    public function linkedReseller()
    {
        return $this->belongsTo(Reseller::class, 'linked_reseller_id');
    }

    /** Inventory items purchased under this expense */
    public function inventoryItems()
    {
        return $this->hasMany(\Plugins\InventoryManager\Models\InventoryItem::class, 'expense_id');
    }

    /** Summary of inventory stock status for this purchase */
    public function getInventorySummaryAttribute(): array
    {
        if ($this->category !== 'item_purchase') {
            return ['tracked' => false, 'count' => 0];
        }

        $items = $this->inventoryItems;
        if ($items->isEmpty()) {
            return ['tracked' => false, 'count' => 0];
        }

        return [
            'tracked'   => true,
            'count'     => $items->count(),
            'total_qty' => (float) $items->sum('quantity'),
            'in_stock'  => (float) $items->where('status', 'in_stock')->sum('quantity'),
            'deployed'  => (float) $items->where('status', 'deployed')->sum('quantity'),
            'damaged'   => (float) $items->where('status', 'damaged')->sum('quantity'),
            'repair'    => (float) $items->where('status', 'under_repair')->sum('quantity'),
        ];
    }

    public function scopeByMonth($query, int $month, int $year)
    {
        return $query->whereMonth('expense_date', $month)
                     ->whereYear('expense_date', $year);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }
}
