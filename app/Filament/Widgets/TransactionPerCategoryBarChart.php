<?php

namespace App\Filament\Widgets;

use App\Models\TransactionCategory;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TransactionPerCategoryBarChart extends ApexChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $chartId = 'transactionPerCategoryBarChart';

    protected static ?string $heading = 'Transaction Per Category';

    protected function getOptions(): array
    {
        $startDate = ! is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;

        $endDate = ! is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        $children = TransactionCategory::where('parent_id', '!=', null)->where('user_id', auth()->user()->id)->get();


//        based on start date and end date
        $childrenWithSumAmountTransaction = $children->map(function ($child) use ($startDate, $endDate) {
            return [
                'name' => $child->name,
                'sumAmount' => $child->transactions()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount'),
            ];
        });

        $categories = $childrenWithSumAmountTransaction->pluck('name')->toArray();
        $series = $childrenWithSumAmountTransaction->pluck('sumAmount')->toArray();


        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Total',
                    'data' => $series,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
    {
        yaxis: {
            labels: {
                formatter: function (val, index) {
                    return 'Rp' + parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 0 });
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val, opt) {
                return 'Rp' + parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 0 });
            },
            dropShadow: {
                enabled: true
            },
        }
    }
    JS);
    }

}
