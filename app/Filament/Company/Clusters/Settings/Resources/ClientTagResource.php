<?php

namespace App\Filament\Company\Clusters\Settings\Resources;

use App\Filament\Company\Clusters\Settings;
use App\Filament\Company\Clusters\Settings\Resources\ClientTagResource\Pages;
use App\Models\Common\ClientTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientTagResource extends Resource
{
    protected static ?string $model = ClientTag::class;

    protected static ?string $cluster = Settings::class;

    // protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 25;

    public static function getNavigationLabel(): string
    {
        return translate('Client Tags');
    }

    public static function getModelLabel(): string
    {
        return translate('client tag');
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
                            ->default('#6366F1'),
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
                    ->sortable()
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientTags::route('/'),
            'create' => Pages\CreateClientTag::route('/create'),
            'edit' => Pages\EditClientTag::route('/{record}/edit'),
        ];
    }
}
