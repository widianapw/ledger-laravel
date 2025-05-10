<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\TransactionPerCategoryBarChart;
use App\Filament\Widgets\TransactionPerTypeChart;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;

class AppDashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

//    protected static string $view = 'filament.pages.dashboard';
//
//    protected static ?string $navigationLabel ="Dashboard";
//    protected static ?string $title = "Dashboard";

    public function getWidgets(): array
    {
        return [
            TransactionPerCategoryBarChart::class,
            TransactionPerTypeChart::class
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->default(Carbon::now()->startOfMonth()),
                        DatePicker::make('endDate')
                            ->default(Carbon::now()->endOfMonth()),
                    ])
                    ->columns(2),
            ]);
    }
}
