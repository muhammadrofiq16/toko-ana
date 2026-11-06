<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion_factor',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function priceTier()
    {
        return $this->hasOne(PriceTier::class);
    }

    /**
     * Get the appropriate price based on mode, quantity, and customer type.
     */
    public function getPriceFor(string $mode = 'eceran', int $qty = 1, ?string $customerType = null): float
    {
        $tier = $this->priceTier;
        if (!$tier) return 0;

        $retailPrice = (float) $tier->price_retail;
        $wholesalePrice = (float) $tier->price_wholesale > 0 ? (float) $tier->price_wholesale : $retailPrice;

        // Condition 1: Mode Grosir OR Reseller Customer gets wholesale price regardless of quantity
        if ($mode === 'grosir' || $customerType === 'reseller') {
            return $wholesalePrice;
        }

        // Condition 2: Retail mode with volume-based threshold (min_wholesale_qty must be > 1)
        $minQty = (int) ($tier->min_wholesale_qty ?? 0);
        if ($minQty > 1 && $qty >= $minQty && (float) $tier->price_wholesale > 0) {
            return (float) $tier->price_wholesale;
        }

        // Default: Retail price
        return $retailPrice;
    }
}
