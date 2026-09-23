<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToolroomResource\Pages;
use App\Models\Toolroom;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ToolroomResource extends Resource
{
    protected static ?string $model = Toolroom::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Sistem & Konfigürasyon';
    protected static ?string $navigationLabel = 'Takımhaneler';
    protected static ?string $modelLabel = 'Takımhane';
    protected static ?string $pluralModelLabel = 'Takımhaneler';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Takımhane Adı')
                ->required()
                ->maxLength(150)
                ->placeholder('Örn: 2. Takımhane (Ağır Vasıta)'),

            TextInput::make('code')
                ->label('Takımhane Kodu')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50)
                ->placeholder('Örn: TKM-AGIR'),

            Textarea::make('description')
                ->label('Açıklama / Kapsam')
                ->rows(3)
                ->columnSpanFull()
                ->placeholder('Bu takımhanede hangi araç gruplarına ait takımlar yer alıyor?'),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Takımhane Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Kod')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono'),

                TextColumn::make('tools_count')
                    ->label('Toplam Parça')
                    ->counts('tools')
                    ->badge()
                    ->color('success'),

                TextColumn::make('blocks_count')
                    ->label('Blok Sayısı')
                    ->counts('blocks')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Durum')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
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
            'index'  => Pages\ListToolrooms::route('/'),
            'create' => Pages\CreateToolroom::route('/create'),
            'edit'   => Pages\EditToolroom::route('/{record}/edit'),
        ];
    }
}
