<?php

namespace App\Filament\Clusters\Products\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Clusters\Products;
use App\Filament\Clusters\Products\Resources\StockTransactionResource\Pages;
use App\Filament\Clusters\Products\Resources\StockTransactionResource\RelationManagers;
use App\Models\Product;
use App\Models\StockTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;

class StockTransactionResource extends Resource
{
    protected static ?string $model = StockTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Products::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\Radio::make('type')
                    ->label('Transaction Type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                    ])
                    ->default('in')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->required()
                    ->suffix('/ml'),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->color(fn($record) => $record->type === 'in' ? 'green' : 'red')
                    ->formatStateUsing(fn($state) => $state === 'in' ? 'Stock In' : 'Stock Out'),
                Tables\Columns\TextColumn::make('quantity')->label('Quantity'),
                Tables\Columns\TextColumn::make('description')->label('Description'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                    ]),
                SelectFilter::make('product_id') // Ubah jadi 'product_id'
                    ->label('Nama Parfum')
                    ->relationship('product', 'name') // Pastikan relasi sudah ada
                    ->multiple()
                    ->preload(),

                FiltersFilter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created From'),
                        DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),


            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('export'),
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                // ExportAction::make()->exporter(DaftarExporter::class)
                FilamentExportHeaderAction::make('export')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransactions::route('/'),
            'create' => Pages\CreateStockTransaction::route('/create'),
            'edit' => Pages\EditStockTransaction::route('/{record}/edit'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [

            FilamentExportBulkAction::make('Export'),

        ];
    }
}
