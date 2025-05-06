<?php

namespace App\Filament\Clusters\Products\Resources\StockTransactionResource\Pages;

use App\Filament\Clusters\Products\Resources\StockTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockTransactions extends ListRecords
{
    protected static string $resource = StockTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
