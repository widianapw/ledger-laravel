<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TransactionPerTypeChart extends ApexChartWidget
{
    use InteractsWithPageFilters;


    protected static ?string $chartId = 'transactionPerTypeChart';

    protected static ?string $heading = 'Transaction Per Type';

    protected function getOptions(): array
    {
        $startDate = !is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;

        $endDate = !is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        $totalIncome = Transaction::where('user_id', auth()->user()->id)->where('transaction_type', TransactionTypeEnum::INCOME->value)->whereBetween('date', [$startDate, $endDate])->sum('amount');

        $totalExpense = Transaction::where('user_id', auth()->user()->id)->where('transaction_type', TransactionTypeEnum::EXPENSE->value)->whereBetween('date', [$startDate, $endDate])->sum('amount');

        $categories = [
            'Income',
            'Expense',
        ];
        $series = [
            $totalIncome,
            $totalExpense
        ];


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
    JS
        );
    }

}
