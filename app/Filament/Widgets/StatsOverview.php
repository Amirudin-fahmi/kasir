<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $product_count = Product::count();
        $order_count = Order::count();
        $omset = Order::sum('total_price');
        $expense = Expense::sum('amount');
        return [
            Stat::make('Product',$product_count),
            Stat::make('Order', $order_count),
            Stat::make('Omset', "Rp ". number_format($omset,0,",",".")),
            Stat::make('Expense',"Rp ".number_format($expense, 0, ",", ".") ),
        ];
    }
}
