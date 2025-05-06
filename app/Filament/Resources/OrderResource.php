<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Utama')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('gender')
                                    ->required()
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ]),

                            ])
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Tambahan')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('birthday'),
                            ])
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        self::getItemsRepeater(),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Tambahan')
                            ->schema([
                                Forms\Components\TextInput::make('total_price')
                                    ->required()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->numeric(),

                                Forms\Components\Textarea::make('note')
                                    ->columnSpanFull(),
                            ])
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Pembayaran')
                            ->schema([
                                Forms\Components\Select::make('payment_method_id')
                                    ->relationship('paymentMethod', 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\get $get) {
                                        $paymentMethod = PaymentMethod::find($state);
                                        $set('is_cash', $paymentMethod->is_cash ?? false);
                                        if (!$paymentMethod->is_cash) {
                                            $set('change_amount', 0);
                                            $set('paid_amount', $get('total_price'));
                                        }
                                    })
                                    ->afterStateHydrated(function ($state, Forms\Set $set, Forms\get $get) {
                                        $paymentMethod = PaymentMethod::find($state);
                                        if (!$paymentMethod?->is_cash) {
                                            $set('paid_amount', $get('total_price'));
                                            $set('change_amount', 0);
                                        }

                                        $set('is_cash', $paymentMethod->is_cash ?? false);
                                    }),
                                Forms\Components\Hidden::make('is_cash')
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('paid_amount')
                                    ->numeric()
                                    ->reactive()
                                    ->label('Nominal bayar')
                                    ->readOnly(fn(Forms\Get $get) => $get('is_cash') == false)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\get $get) {
                                        // menghitung uang kembalian
                                        self::updateExchangePaid($set, $get);
                                    }),
                                Forms\Components\TextInput::make('change_amount')
                                    ->numeric()
                                    ->label('Kembalian')
                                    ->readOnly(),
                            ])
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship('paymentMethod', 'name')
                    ->options([
                        'QRIS' => 'QRIS', 
                        'Tunai' => 'Tunai',
                    ]),


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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }



    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('orderProducts')
            ->relationship()
            ->live()
            ->columns([
                'md' => 10,
            ])
            ->afterStateUpdated(function (Forms\Set $set, Forms\get $get) {
                self::updateTotalPrice($set, $get);
            })
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->required()
                    ->options(Product::query()->where('stock', '>', 1)->pluck('name', 'id'))
                    ->afterStateHydrated(function ($state, Forms\Set $set, Forms\get $get) {
                        $product = Product::find($state);
                        $set('unit_price', $product->price ?? 0);
                        $set('stock', $product->stock ?? 0);
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\get $get) {
                        $product = Product::find($state);
                        $set('unit_price', $product->price ?? 0);
                        $set('stock', $product->stock ?? 0);

                        self::updateTotalPrice($set, $get);
                    })
                    ->columnSpan([
                        'md' => 4,
                    ])
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->suffix('\ml')
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\get $get) {
                        $stock = $get('stock');
                        if ($state > $stock) {
                            $set('quantity', $stock);
                            Notification::make()
                                ->title('Stock tidak mencukupi')
                                ->warning()
                                ->send();
                        }
                        self::updateTotalPrice($set, $get);
                    }),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->columnSpan([
                        'md' => 2,
                    ]),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga saat ini')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->suffix('/ml')
                    ->columnSpan([
                        'md' => 2,
                    ]),
            ]);
    }

    protected static function updateTotalPrice(Forms\Set $set, Forms\get $get)
    {
        $selectedProduct = collect($get('orderProducts'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
        $price = Product::find($selectedProduct->pluck('product_id'))->pluck('price', 'id');

        $total = $selectedProduct->reduce(function ($total, $product) use ($price) {
            return $total + ($product['quantity'] * $price[$product['product_id']]);
        });

        $set('total_price', $total);
    }

    protected static function updateExchangePaid(Forms\Set $set, Forms\get $get): void
    {
        $paidAmount = (int) $get('paid_amount') ?? 0;
        $totalPrice = (int) $get('total_price') ?? 0;

        $exchange = $paidAmount - $totalPrice;

        $set('change_amount', $exchange);
    }
}
