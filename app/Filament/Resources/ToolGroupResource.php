<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToolGroupResource\Pages;
use App\Models\ToolGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ToolGroupResource extends Resource
{
    protected static ?string $model = ToolGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Sistem & Konfigürasyon';
    protected static ?string $navigationLabel = 'Parça Grupları';
    protected static ?string $modelLabel = 'Parça Grubu';
    protected static ?string $pluralModelLabel = 'Parça Grupları';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Grup Adı')
                ->required()
                ->maxLength(100)
                ->placeholder('Örn: Hafif Ticari Araçlar (HTA)'),

            TextInput::make('code')
                ->label('Grup Kodu')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50)
                ->placeholder('Örn: HTA'),

            Select::make('color')
                ->label('Rozet Rengi')
                ->options([
                    'primary' => 'Turuncu (Primary)',
                    'warning' => 'Sarı / Kehribar (Warning)',
                    'info'    => 'Mavi (Info)',
                    'success' => 'Yeşil (Success)',
                    'danger'  => 'Kırmızı (Danger)',
                    'gray'    => 'Gri (Gray)',
                ])
                ->default('warning')
                ->required(),

            Textarea::make('description')
                ->label('Açıklama')
                ->rows(2)
                ->columnSpanFull()
                ->placeholder('Bu grupta hangi araçların takımları yer alır? (Örn: Rot çektirme, aks aparatları vb.)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Grup Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Kod')
                    ->badge()
                    ->color(fn (ToolGroup $record) => $record->color ?: 'gray')
                    ->fontFamily('mono'),

                TextColumn::make('tools_count')
                    ->label('Tanımlı Parça')
                    ->counts('tools')
                    ->badge()
                    ->color('info'),

                TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListToolGroups::route('/'),
            'create' => Pages\CreateToolGroup::route('/create'),
            'edit'   => Pages\EditToolGroup::route('/{record}/edit'),
        ];
    }
}
