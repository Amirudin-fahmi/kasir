<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Faker\Core\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;


class ProductAlert extends BaseWidget
{

    protected static ?int $sort = 3;
    public function table(Table $table): Table
    {
        return $table
            ->query(
               Product::query()->where('stock' , '<=', 2000)->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('stock')
                    ->label('Stock')
                    ->color(static function ($state): string {
                        if ($state < 1000) {
                            return 'danger';
                        } elseif ($state < 2000) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->sortable()
                    
                ])
                ->defaultPaginationPageOption(5);

    }
}
