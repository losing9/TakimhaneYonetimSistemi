<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlotResource\Pages;
use App\Models\Slot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SlotResource extends Resource
{
    protected static ?string $model = Slot::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Konum Yönetimi';
    protected static ?string $navigationLabel = 'Gözler';
    protected static ?string $modelLabel = 'Göz';
    protected static ?string $pluralModelLabel = 'Gözler';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('shelf_id')
                ->label('Raf')
                ->relationship('shelf', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_label)
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('name')
                ->label('Göz Adı')
                ->required()
                ->maxLength(100)
                ->placeholder('Örn: Göz 1'),

            TextInput::make('capacity')
                ->label('Kapasite (Parça)')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shelf.block.name')
                    ->label('Blok')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('shelf.name')
                    ->label('Raf')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('name')
                    ->label('Göz Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Kapasite')
                    ->alignCenter(),

                TextColumn::make('tools_count')
                    ->label('Mevcut Parça')
                    ->counts('tools')
                    ->badge()
                    ->color(fn ($state, $record) => $state >= $record->capacity ? 'danger' : 'success')
                    ->action(
                        Action::make('view_tools_from_count')
                            ->modalHeading(fn (Slot $record) => '📦 ' . $record->full_label . ' — Takım Listesi')
                            ->modalContent(fn (Slot $record) => view('filament.resources.slots.tools-modal', ['record' => $record]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Kapat')
                            ->modalWidth('2xl')
                    ),
            ])
            ->recordAction('view_tools')
            ->filters([
                SelectFilter::make('shelf')
                    ->relationship('shelf', 'name')
                    ->label('Raf'),
            ])
            ->actions([
                Action::make('view_tools')
                    ->label('Takımları Gör')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('primary')
                    ->modalHeading(fn (Slot $record) => '📦 ' . $record->full_label . ' — Takım Listesi')
                    ->modalContent(fn (Slot $record) => view('filament.resources.slots.tools-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kapat')
                    ->modalWidth('2xl'),

                ActionGroup::make([
                    EditAction::make(),
                    Action::make('shelf_label_pdf')
                        ->label('📦 Raf Etiketi PDF')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->url(fn (Slot $record) => route('labels.slot.pdf', $record))
                        ->openUrlInNewTab(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSlots::route('/'),
            'create' => Pages\CreateSlot::route('/create'),
            'edit'   => Pages\EditSlot::route('/{record}/edit'),
        ];
    }
}
