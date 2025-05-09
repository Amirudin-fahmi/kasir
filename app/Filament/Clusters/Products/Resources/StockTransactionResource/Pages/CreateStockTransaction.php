<?php

namespace App\Filament\Clusters\Products\Resources\StockTransactionResource\Pages;

use App\Filament\Clusters\Products\Resources\StockTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockTransaction extends CreateRecord
{
    protected static string $resource = StockTransactionResource::class;
}
