<?php

namespace App\Filament\Resources;

use App\Enums\TransactionTypeEnum;
use App\Filament\Resources\TransactionCategoryResource\Pages;
use App\Filament\Resources\TransactionCategoryResource\RelationManagers;
use App\Models\TransactionCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use function Symfony\Component\Translation\t;

class TransactionCategoryResource extends Resource
{
    protected static ?string $model = TransactionCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->user()->id)->where('parent_id', null);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make("name")
                        ->placeholder("Name")
                        ->required(),
                    Forms\Components\Textarea::make("description")
                        ->placeholder("Description")
                        ->hint("Optional")
                        ->nullable(),
                    Forms\Components\TextInput::make("monthly_budget")
                        ->placeholder("Monthly Budget")
                        ->numeric()
                        ->nullable(),

                    Forms\Components\Repeater::make("children")
                        ->relationship("children")
                        ->schema([
                            Forms\Components\TextInput::make("name")
                                ->placeholder("Name")
                                ->required(),
                            Forms\Components\Toggle::make("is_active")
                                ->default(true)
                                ->label("Active")
                        ])
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['user_id'] = auth()->user()->id;
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                            $data['user_id'] = auth()->user()->id;
                            return $data;
                        })
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")->searchable(),
                Tables\Columns\TextColumn::make("description")->limit(40),
                Tables\Columns\TextColumn::make("children.name")->searchable(),
                Tables\Columns\TextColumn::make("monthly_budget")->formatStateUsing(function ($state) {
                    return 'Rp' . number_format($state, 0, ',', '.');
                }),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
            ])
            ->defaultSort("id", "desc");
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
            'index' => Pages\ListTransactionCategories::route('/'),
            'create' => Pages\CreateTransactionCategory::route('/create'),
            'edit' => Pages\EditTransactionCategory::route('/{record}/edit'),
        ];
    }
}
