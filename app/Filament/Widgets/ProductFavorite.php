<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductFavorite extends BaseWidget
{

    protected static ?int $sort = 4;
    public function table(Table $table): Table
    {   
        $productQuery = Product::query()
            ->withCount('orderProducts')
            ->orderByDesc('order_products_count')
            ->limit(5);
        return $table
            ->query(
                $productQuery
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('order_products_count'),
            ]
            )
            ->defaultPaginationPageOption(5);
    }
}
