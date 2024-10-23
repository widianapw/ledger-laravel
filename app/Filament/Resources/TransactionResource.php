<?php

namespace App\Filament\Resources;

use App\Enums\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;


class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where("user_id", auth()->user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make("transaction_category_id")
                        ->searchable()
                        ->label("Category")
                        ->required()
                        ->options(TransactionCategory::getCategoryOptionGroup()),
                    Forms\Components\Select::make("transaction_type")
                        ->label("Type")
                        ->required()
                        ->options(TransactionTypeEnum::class),
                    Forms\Components\TextInput::make("amount")
                        ->required()
                        ->numeric(),
                    Forms\Components\Textarea::make("description")
                        ->hint("Optional"),
                    Forms\Components\DatePicker::make("date")
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("date"),
                Tables\Columns\TextColumn::make("transactionCategory.name")->label("Category")->searchable(),
                Tables\Columns\TextColumn::make("transaction_type")->label("Type")->badge(),
                Tables\Columns\TextColumn::make("amount")->formatStateUsing(function ($state) {
                    return 'Rp' . number_format($state, 0, ',', '.');
                })->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label("Total")
                        ->formatStateUsing(function ($state) {
                            return 'Rp' . number_format($state, 0, ',', '.');
                        }),

//                    Tables\Columns\Summarizers\Sum::make()
//                        ->label("Total Expense")
//                        ->query(fn(QueryBuilder $query) => $query->where('transaction_type', TransactionTypeEnum::EXPENSE))
//                        ->formatStateUsing(function ($state) {
//                            return 'Rp' . number_format($state, 0, ',', '.');
//                        }),
//                    Tables\Columns\Summarizers\Sum::make()
//                        ->label("Total Income")
//                        ->query(fn(QueryBuilder $query) => $query->where('transaction_type', TransactionTypeEnum::INCOME))
//                        ->formatStateUsing(function ($state) {
//                            return 'Rp' . number_format($state, 0, ',', '.');
//                        })
                ]),

                Tables\Columns\TextColumn::make("description")->searchable()->limit(40),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make("transaction_category_id")
                    ->label("Category")
                    ->multiple()
                    ->options(TransactionCategory::getCategoryOptionGroup()),
                Tables\Filters\SelectFilter::make("transaction_type")
                    ->label("Type")
                    ->options(TransactionTypeEnum::class),
                Tables\Filters\Filter::make('created_at')
                    ->columnSpan(2)
                    ->form([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('from')
                                    ->default(Carbon::now()->startOfMonth())
                                ,
                                Forms\Components\DatePicker::make('until')
                                    ->default(Carbon::now()->endOfMonth())
                                ,
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Trx from ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Trx until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    })

            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
        ];
    }
}
