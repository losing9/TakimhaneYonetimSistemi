<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StationResource\Pages;
use App\Models\Station;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StationResource extends Resource
{
    protected static ?string $model = Station::class;
    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'İstasyonlar';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int    $navigationSort  = 91;
    protected static ?string $modelLabel       = 'İstasyon';
    protected static ?string $pluralModelLabel = 'İstasyonlar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('İstasyon Bilgileri')->schema([
                Forms\Components\Select::make('toolroom_id')
                    ->label('Takımhane')
                    ->relationship('toolroom', 'name')
                    ->required()
                    ->default(1)
                    ->preload(),

                Forms\Components\TextInput::make('name')
                    ->label('İstasyon Adı')
                    ->required()
                    ->placeholder('Örn: Takımhane A Girişi')
                    ->maxLength(100),

                Forms\Components\TextInput::make('location')
                    ->label('Konum Açıklaması')
                    ->placeholder('Örn: Zemin kat, kuzey kapısı')
                    ->maxLength(200),

                Forms\Components\TextInput::make('qr_token')
                    ->label('Kalıcı QR Token')
                    ->helperText('Boş bırakırsanız otomatik oluşturulur.')
                    ->maxLength(100)
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('toolroom.name')
                    ->label('Takımhane')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('İstasyon Adı')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Konum')
                    ->default('—'),

                Tables\Columns\TextColumn::make('qr_token')
                    ->label('QR Token')
                    ->copyable()
                    ->limit(20),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('live_screen')
                    ->label('📺 Canlı Dinamik QR')
                    ->icon('heroicon-o-tv')
                    ->color('warning')
                    ->tooltip('Bu haftanın değişen dinamik karekod ekranını açar')
                    ->url(fn (Station $record) => route('stations.display', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('print_qr')
                    ->label('QR Yazdır')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn (Station $record) => route('station.qr', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Yeni İstasyon'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStations::route('/'),
            'create' => Pages\CreateStation::route('/create'),
            'edit'   => Pages\EditStation::route('/{record}/edit'),
        ];
    }
}
