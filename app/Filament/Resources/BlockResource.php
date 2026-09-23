<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockResource\Pages;
use App\Models\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Konum Yönetimi';
    protected static ?string $navigationLabel = 'Bloklar';
    protected static ?string $modelLabel = 'Blok';
    protected static ?string $pluralModelLabel = 'Bloklar';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            \Filament\Forms\Components\Select::make('toolroom_id')
                ->label('Takımhane')
                ->relationship('toolroom', 'name')
                ->required()
                ->default(1)
                ->preload(),

            TextInput::make('name')
                ->label('Blok Adı')
                ->required()
                ->maxLength(100)
                ->placeholder('Örn: Blok A'),

            Textarea::make('description')
                ->label('Açıklama')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('toolroom.name')
                    ->label('Takımhane')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Blok Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shelves_count')
                    ->label('Raf Sayısı')
                    ->counts('shelves')
                    ->badge()
                    ->color('info'),

                TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Eklenme')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlocks::route('/'),
            'create' => Pages\CreateBlock::route('/create'),
            'edit'   => Pages\EditBlock::route('/{record}/edit'),
        ];
    }
}
