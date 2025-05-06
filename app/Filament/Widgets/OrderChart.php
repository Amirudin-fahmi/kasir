<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Order;
use Carbon\Carbon;

class OrderChart extends ChartWidget
{
    protected static ?string $heading = 'Omset';

    protected static ?int $sort = 1;

    protected static string $color = 'success';

    public ?string $filter = 'today';


    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $dataRange = match ($activeFilter) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'period' => 'perHour',
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'period' => 'perDay',
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'period' => 'perDay',
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'period' => 'perMonth',
            ],
            default => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'period' => 'perHour',
            ],
        };

        $query = Trend::model(Order::class)
            ->between(
                start: $dataRange['start'],
                end: $dataRange['end'],
            );

        // Perbaikan pengecekan periode
        if ($dataRange['period'] === 'perHour') {
            $data = $query->perHour();
        } elseif ($dataRange['period'] === 'perDay') {
            $data = $query->perDay();
        } else {
            $data = $query->perMonth();
        }

        $data = $data->sum('total_price');

        $labels = $data->map(function (TrendValue $value) use ($dataRange) {
            $date = Carbon::parse($value->date);

            if ($dataRange['period'] === 'perHour') {
                return $date->format('H:i');
            } elseif ($dataRange['period'] === 'perDay') {
                return $date->format('d M');
            } else {
                return $date->format('M Y');
            }
        });

        return [
            'datasets' => [
                [
                    'label' => 'Omset ' . ($this->getFilters()[$activeFilter] ?? ''),
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $labels->toArray(), // Convert ke array agar aman
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
   
}
