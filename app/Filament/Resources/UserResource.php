<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Kullanıcılar';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int    $navigationSort  = 90;
    protected static ?string $modelLabel       = 'Kullanıcı';
    protected static ?string $pluralModelLabel = 'Kullanıcılar';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kullanıcı Bilgileri')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ad Soyad')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('email')
                    ->label('E-posta')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->required()
                    ->options([
                        'super_admin'   => '👑 Süper Admin',
                        'takimhane_sor' => '🔑 Takımhane Sorumlusu',
                        'personel'      => '👤 Personel',
                    ])
                    ->default('personel')
                    ->reactive(),

                Forms\Components\Select::make('toolroom_id')
                    ->label('Sorumlu Olduğu Takımhane')
                    ->relationship('toolroom', 'name')
                    ->helperText('Takımhane sorumlusu rolü için zorunludur. Boş bırakılırsa tüm takımhaneler yetkisi (Süper Admin) geçerli olur.')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('personnel_id')
                    ->label('Bağlı Personel Kaydı')
                    ->helperText('Personel rolündeki kullanıcılar için zimmet takibi yapmak amacıyla')
                    ->options(fn () => \App\Models\Personnel::pluck('name', 'id'))
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record) {
                            $person = \App\Models\Personnel::where('user_id', $record->id)->first();
                            $component->state($person?->id);
                        }
                    })
                    ->dehydrated(false)
                    ->searchable()
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Şifre')->schema([
                Forms\Components\TextInput::make('password')
                    ->label('Şifre')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create')
                    ->minLength(8)
                    ->helperText('Düzenlerken boş bırakırsanız şifre değişmez.')
                    ->revealable(),

                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Şifre Tekrar')
                    ->password()
                    ->same('password')
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrated(false)
                    ->revealable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ad Soyad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'super_admin'   => 'warning',
                        'takimhane_sor' => 'success',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'super_admin'   => 'Super Admin',
                        'takimhane_sor' => 'Takimhane Sor.',
                        'personel'      => 'Personel',
                        default         => $state,
                    }),

                Tables\Columns\TextColumn::make('toolroom.name')
                    ->label('Sorumlu Takımhane')
                    ->badge()
                    ->color('info')
                    ->placeholder('Tüm Takımhaneler (Genel)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('personnel.name')
                    ->label('Personel Kaydı')
                    ->default('—')
                    ->placeholder('Bağlı değil'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'super_admin'   => 'Süper Admin',
                        'takimhane_sor' => 'Takımhane Sorumlusu',
                        'personel'      => 'Personel',
                    ]),
                Tables\Filters\SelectFilter::make('toolroom_id')
                    ->label('Takımhane')
                    ->relationship('toolroom', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('reset_password')
                    ->label('Şifre Sıfırla')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('Yeni Şifre')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->revealable(),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['password' => Hash::make($data['new_password'])]);
                        Notification::make()->title('Şifre güncellendi')->success()->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Şifre Sıfırla'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
