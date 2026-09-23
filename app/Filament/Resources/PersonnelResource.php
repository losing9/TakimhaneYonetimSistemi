<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonnelResource\Pages;
use App\Models\Personnel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PersonnelResource extends Resource
{
    protected static ?string $model = Personnel::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Parça Yönetimi';
    protected static ?string $navigationLabel = 'Personeller';
    protected static ?string $modelLabel = 'Personel';
    protected static ?string $pluralModelLabel = 'Personeller';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Ad Soyad')
                ->required()
                ->maxLength(200),

            TextInput::make('badge_number')
                ->label('Sicil No')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            TextInput::make('department')
                ->label('Departman / Bölüm')
                ->maxLength(150),

            TextInput::make('email')
                ->label('E-Posta')
                ->email()
                ->maxLength(200),

            TextInput::make('phone')
                ->label('Telefon')
                ->tel()
                ->maxLength(30),

            \Filament\Forms\Components\Select::make('user_id')
                ->label('Kullanıcı Hesabı')
                ->relationship('user', 'name', fn ($query) => $query->where('role', 'personel'))
                ->searchable()
                ->nullable()
                ->helperText('Bu personelin portal üzerinden giriş yapabilmesi için kullanıcı hesabı bağlayın.'),

            Toggle::make('is_active')
                ->label('Aktif Personel')
                ->default(true),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Personel Bilgileri')
                ->columns(3)
                ->schema([
                    TextEntry::make('name')->label('Ad Soyad'),
                    TextEntry::make('badge_number')->label('Sicil No'),
                    TextEntry::make('department')->label('Departman')->placeholder('—'),
                    TextEntry::make('email')->label('E-Posta')->placeholder('—'),
                    TextEntry::make('phone')->label('Telefon')->placeholder('—'),
                    IconEntry::make('is_active')
                        ->label('Durum')
                        ->boolean()
                        ->trueLabel('Aktif')
                        ->falseLabel('Pasif'),
                ]),

            Section::make('Aktif Zimmetler')
                ->schema([
                    TextEntry::make('activeLoans.tool.name')
                        ->label('Zimmetli Parçalar')
                        ->listWithLineBreaks()
                        ->placeholder('Aktif zimmet yok.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad Soyad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('badge_number')
                    ->label('Sicil No')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('department')
                    ->label('Departman')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label('E-Posta')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('active_loans_count')
                    ->label('Aktif Zimmet')
                    ->counts('activeLoans')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktiflik Durumu')
                    ->trueLabel('Sadece Aktif')
                    ->falseLabel('Sadece Pasif'),
            ])
            ->actions([
                ViewAction::make(),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('id_card_pdf')
                        ->label('🪖 Kimlik Kartı PDF')
                        ->icon('heroicon-o-identification')
                        ->color('info')
                        ->url(fn (Personnel $record) => route('labels.personnel.pdf', $record))
                        ->openUrlInNewTab(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPersonnel::route('/'),
            'create' => Pages\CreatePersonnel::route('/create'),
            'edit'   => Pages\EditPersonnel::route('/{record}/edit'),
            'view'   => Pages\ViewPersonnel::route('/{record}'),
        ];
    }
}
