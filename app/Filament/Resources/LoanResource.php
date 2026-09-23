<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use App\Models\Tool;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationGroup = 'Zimmet İşlemleri';
    protected static ?string $navigationLabel = 'Zimmetler';
    protected static ?string $modelLabel = 'Zimmet';
    protected static ?string $pluralModelLabel = 'Zimmetler';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('tool_id')
                ->label('Parça')
                ->options(
                    // Ödünç verme formunda sadece "Mevcut" parçalar göster
                    fn () => Tool::available()->pluck('name', 'id')
                )
                ->required()
                ->searchable()
                ->preload()
                ->helperText('Sadece "Mevcut" durumundaki parçalar listelenir.'),

            Select::make('personnel_id')
                ->label('Personel')
                ->relationship('personnel', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->badge_number})")
                ->required()
                ->searchable()
                ->preload(),

            DateTimePicker::make('loaned_at')
                ->label('Ödünç Alma Tarihi')
                ->required()
                ->default(now())
                ->displayFormat('d.m.Y H:i'),

            DateTimePicker::make('planned_return_at')
                ->label('Planlanan İade Tarihi')
                ->required()
                ->displayFormat('d.m.Y H:i')
                ->after('loaned_at'),

            Textarea::make('notes')
                ->label('Notlar')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tool.name')
                    ->label('Parça')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('personnel.name')
                    ->label('Personel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('personnel.badge_number')
                    ->label('Sicil No')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('loaned_at')
                    ->label('Ödünç Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('planned_return_at')
                    ->label('Planlanan İade')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Gerçek İade')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Henüz iade edilmedi')
                    ->toggleable(),

                TextColumn::make('returnStation.name')
                    ->label('İade İstasyonu')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\ImageColumn::make('return_photo')
                    ->label('İade Fotoğrafı')
                    ->disk('public')
                    ->square()
                    ->size(44)
                    ->placeholder('—')
                    ->tooltip('🔍 Fotoğrafı büyütmek için tıklayın')
                    ->extraImgAttributes(['class' => 'cursor-pointer hover:opacity-85 hover:scale-105 transition rounded-lg'])
                    ->action(
                        \Filament\Tables\Actions\Action::make('preview_return_photo')
                            ->modalHeading(fn (Loan $record) => '📸 İade Fotoğrafı — ' . ($record->tool?->name ?? 'Zimmet'))
                            ->modalDescription(fn (Loan $record) => 'Personel: ' . ($record->personnel?->name ?? '—') . ' • İade: ' . ($record->returned_at?->format('d.m.Y H:i') ?? '—'))
                            ->modalContent(fn (Loan $record) => view('filament.components.image-modal', [
                                'record' => (object)[
                                    'id' => $record->id,
                                    'name' => 'İade Fotoğrafı: ' . ($record->tool?->name ?? ''),
                                    'image' => $record->return_photo,
                                    'serial_no' => $record->tool?->serial_no ?? null,
                                    'location_label' => $record->returnStation?->name ?? null,
                                ],
                            ]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Kapat')
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('loanedByUser.name')
                    ->label('Alan Kullanıcı')
                    ->placeholder('Yönetici (Manuel)')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_label')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (Loan $record) => match ($record->status) {
                        'active'   => 'warning',
                        'returned' => 'success',
                        'overdue'  => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->defaultSort('loaned_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'active'   => 'Ödünçte',
                        'returned' => 'İade Edildi',
                        'overdue'  => 'Gecikmede',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                // İade Et butonu — sadece aktif/gecikmiş kayıtlarda göster
                Action::make('return')
                    ->label('İade Et')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Loan $record) => in_array($record->status, ['active', 'overdue']))
                    ->form([
                        Select::make('returned_slot_id')
                            ->label('İade Konumu (Göz)')
                            ->relationship('returnedSlot', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_label)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Parçanın teslim edildiği gözü seçin.'),

                        Textarea::make('return_notes')
                            ->label('İade Notu')
                            ->rows(2),
                    ])
                    ->action(function (Loan $record, array $data) {
                        $record->update([
                            'status'           => 'returned',
                            'returned_at'      => now(),
                            'returned_slot_id' => $data['returned_slot_id'],
                            'notes'            => $record->notes
                                ? $record->notes . "\n[İade]: " . ($data['return_notes'] ?? '')
                                : ($data['return_notes'] ?? null),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Parçayı İade Al')
                    ->modalDescription('Parçanın iade edildiği konumu doğrulayın.'),

                EditAction::make()
                    ->visible(fn (Loan $record) => $record->status !== 'returned'),
            ])
            ->striped()
            ->recordClasses(fn (Loan $record) => match ($record->status) {
                'overdue' => 'bg-red-50 dark:bg-red-950/20',
                default   => null,
            });
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Zimmet Bilgileri')
                    ->columns(3)
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('tool.name')->label('Parça'),
                        \Filament\Infolists\Components\TextEntry::make('personnel.name')->label('Personel'),
                        \Filament\Infolists\Components\TextEntry::make('status_label')
                            ->label('Durum')
                            ->badge()
                            ->color(fn (Loan $record) => match ($record->status) {
                                'active'   => 'warning',
                                'returned' => 'success',
                                'overdue'  => 'danger',
                                default    => 'gray',
                            }),
                    ]),

                \Filament\Infolists\Components\Section::make('Ödünç Alma Detayları')
                    ->columns(3)
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('loaned_at')->label('Ödünç Tarihi')->dateTime('d.m.Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('planned_return_at')->label('Planlanan İade Tarihi')->dateTime('d.m.Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('loanedByUser.name')
                            ->label('Ödünç Alan Kullanıcı')
                            ->placeholder('Yönetici (Manuel)'),
                    ]),

                \Filament\Infolists\Components\Section::make('İade Detayları')
                    ->columns(3)
                    ->visible(fn (Loan $record) => $record->status === 'returned')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('returned_at')->label('İade Tarihi')->dateTime('d.m.Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('returnStation.name')
                            ->label('İade Edilen İstasyon')
                            ->placeholder('—'),
                        \Filament\Infolists\Components\TextEntry::make('return_condition_notes')
                            ->label('İade Durum Notu')
                            ->placeholder('—'),
                        \Filament\Infolists\Components\ImageEntry::make('return_photo')
                            ->label('İade Fotoğrafı')
                            ->disk('public')
                            ->columnSpanFull()
                            ->height(250)
                            ->square(false),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'view'   => Pages\ViewLoan::route('/{record}'),
        ];
    }

    /**
     * Yeni zimmet oluştururken panel kullanıcısını kaydet
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status']     = 'active';
        return $data;
    }
}
