<?php

namespace App\Filament\Company\Clusters\Settings\Resources;

use App\Filament\Company\Clusters\Settings;
use App\Filament\Company\Clusters\Settings\Resources\ClientCategoryResource\Pages;
use App\Models\Common\ClientCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientCategoryResource extends Resource
{
    protected static ?string $model = ClientCategory::class;

    protected static ?string $cluster = Settings::class;

    protected static ?string $navigationIcon = 'heroicon-o-ellipsis-vertical';

    protected static ?int $navigationSort = 24;

    public static function getNavigationLabel(): string
    {
        return translate('Client Categories');
    }

    public static function getModelLabel(): string
    {
        return translate('client category');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Color')
                            ->required()
                            ->default('#0EA5E9'),
                    ])->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query) => $query->withCount('clients'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Color')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('clients_count')
                    ->label('Clients')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientCategories::route('/'),
            'create' => Pages\CreateClientCategory::route('/create'),
            'edit' => Pages\EditClientCategory::route('/{record}/edit'),
        ];
    }
}
