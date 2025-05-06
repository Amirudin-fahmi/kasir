<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    protected $fillable = ['product_id', 'type', 'quantity', 'description'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
