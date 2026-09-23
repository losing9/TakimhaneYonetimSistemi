<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShelfResource\Pages;
use App\Models\Shelf;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShelfResource extends Resource
{
    protected static ?string $model = Shelf::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Konum Yönetimi';
    protected static ?string $navigationLabel = 'Raflar';
    protected static ?string $modelLabel = 'Raf';
    protected static ?string $pluralModelLabel = 'Raflar';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('block_id')
                ->label('Blok')
                ->relationship('block', 'name', function (\Illuminate\Database\Eloquent\Builder $query) {
                    if ($roomId = auth()->user()?->toolroom_id) {
                        $query->where('toolroom_id', $roomId);
                    }
                })
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('name')
                ->label('Raf Adı')
                ->required()
                ->maxLength(100)
                ->placeholder('Örn: Raf 1'),

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
                TextColumn::make('block.name')
                    ->label('Blok')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Raf Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slots_count')
                    ->label('Göz Sayısı')
                    ->counts('slots')
                    ->badge()
                    ->color('info'),

                TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('block')
                    ->relationship('block', 'name', function (\Illuminate\Database\Eloquent\Builder $query) {
                        if ($roomId = auth()->user()?->toolroom_id) {
                            $query->where('toolroom_id', $roomId);
                        }
                    })
                    ->label('Blok'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if ($roomId = auth()->user()?->toolroom_id) {
            $query->whereHas('block', fn ($q) => $q->where('toolroom_id', $roomId));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListShelves::route('/'),
            'create' => Pages\CreateShelf::route('/create'),
            'edit'   => Pages\EditShelf::route('/{record}/edit'),
        ];
    }
}
