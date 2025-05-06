<?php

namespace App\Models;

use App\Models\StockTransaction as ModelsStockTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\OrderProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use StockTransaction;

#[ObservedBy([OrderProductObserver::class])]
class OrderProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    protected static function boot()
    {
        parent::boot();
        static::created(function ($orderProduct) {
            ModelsStockTransaction::create([
                'product_id' => $orderProduct->product_id,
                'type' => 'out',
                'quantity' => $orderProduct->quantity,
                'description' => 'Terjual ke pelanggan',
            ]);
        });
    }
}
