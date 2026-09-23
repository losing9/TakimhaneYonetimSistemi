<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToolResource\Pages;
use App\Models\Tool;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ToolResource extends Resource
{
    protected static ?string $model          = Tool::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Parça Yönetimi';
    protected static ?string $navigationLabel = 'Parçalar / Takımlar';
    protected static ?string $modelLabel      = 'Parça';
    protected static ?string $pluralModelLabel = 'Parçalar';
    protected static ?int    $navigationSort  = 1;

    // =========================================================================
    // FORM (Oluşturma & Düzenleme)
    // =========================================================================

    public static function form(Form $form): Form
    {
        $isCreate = $form->getOperation() === 'create';

        return $form->schema([

            // ── Temel Bilgiler ────────────────────────────────────────────────
            Section::make('🔧 Temel Bilgiler')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Parça Adı')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull()
                        ->placeholder('Örn: Tornavida Set, Dijital Kumpas'),

                    Select::make('toolroom_id')
                        ->label('🏢 Takımhane')
                        ->relationship('toolroom', 'name')
                        ->required()
                        ->default(1)
                        ->searchable()
                        ->preload(),

                    Select::make('tool_group_id')
                        ->label('🏷️ Araç / Parça Grubu')
                        ->relationship('toolGroup', 'name')
                        ->placeholder('Grup seçin (Örn: HTA, Binek, Ağır Vasıta...)')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('category')
                        ->label('Kategori')
                        ->options(Tool::CATEGORIES)
                        ->searchable()
                        ->nullable()
                        ->placeholder('Kategori seçin (isteğe bağlı)'),

                    Select::make('status')
                        ->label('Durum')
                        ->options([
                            'available'   => '✅ Mevcut',
                            'loaned'      => '📤 Ödünçte',
                            'maintenance' => '🔧 Bakımda',
                            'scrapped'    => '🗑️ Hurda',
                        ])
                        ->required()
                        ->default('available'),
                ]),

            // ── Kimlik Bilgileri (Seri No & Barkod) ──────────────────────────
            Section::make('🔖 Seri No & Barkod / QR')
                ->description('Boş bırakırsanız sistem otomatik olarak TKM-' . now()->year . '-XXXXX formatında üretir.')
                ->columns(2)
                ->schema([
                    TextInput::make('serial_no')
                        ->label('Seri Numarası')
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->placeholder($isCreate ? 'Boş bırakılırsa otomatik üretilir' : '')
                        ->helperText(function ($state, $record) {
                            if (!$state) {
                                return '⚡ Boş bırakırsanız: TKM-' . now()->year . '-XXXXX formatında otomatik atanır.';
                            }
                            $query = Tool::where('serial_no', $state);
                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }
                            $count = $query->count();
                            if ($count > 0) {
                                return "⚠️ DİKKAT: Bu seri numarasına ({$state}) sahip {$count} adet alet zaten var! Kaydedildiğinde yeni bir parça eklenerek envanter stoğu artacaktır.";
                            }
                            return null;
                        }),

                    TextInput::make('barcode')
                        ->label('Barkod Değeri (QR İçeriği)')
                        ->maxLength(100)
                        ->placeholder($isCreate ? 'Boş bırakılırsa seri no kullanılır' : '')
                        ->helperText('Boş bırakılırsa Seri No ile aynı değer kullanılır.'),

                    Placeholder::make('scanner')
                        ->label('Kamera İle Oku')
                        ->content(fn () => new \Illuminate\Support\HtmlString(view('filament.components.scanner')->render()))
                        ->columnSpanFull(),
                ]),


            // ── Konum & Süre ─────────────────────────────────────────────────
            Section::make('📍 Konum & Ödünç Ayarları')
                ->columns(2)
                ->schema([
                    Select::make('slot_id')
                        ->label('Konum (Raf Gözü)')
                        ->relationship('slot', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_label)
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Parça ödünçte ise boş bırakın.'),

                    TextInput::make('max_loan_days')
                        ->label('Maks. Ödünç Süresi')
                        ->numeric()
                        ->required()
                        ->default(7)
                        ->minValue(1)
                        ->suffix('gün')
                        ->helperText('Bu süre aşılırsa zimmet "Gecikmiş" olarak işaretlenir.'),
                ]),

            // ── Fotoğraf & Açıklama ───────────────────────────────────────────
            Section::make('🖼️ Fotoğraf & Açıklama')
                ->columns(1)
                ->schema([
                    FileUpload::make('image')
                        ->label('Parça Fotoğrafı')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('tools')
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Etiket üzerinde ve detay sayfasında görünür. (Max: 4 MB, yalnızca JPEG/PNG/WEBP)')
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Açıklama / Notlar')
                        ->rows(3)
                        ->placeholder('Parça hakkında ek notlar, teknik bilgiler vb.')
                        ->columnSpanFull(),
                ]),

        ]);
    }

    // =========================================================================
    // INFOLIST (Görüntüleme — QR panel dahil)
    // =========================================================================

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // Sol kolon — ana bilgiler
            InfoSection::make('Parça Bilgileri')
                ->columnSpan(['default' => 3, 'lg' => 2])
                ->columns(['default' => 1, 'sm' => 2, 'md' => 3])
                ->schema([
                    TextEntry::make('name')
                        ->label('Parça Adı')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),

                    TextEntry::make('category_label')
                        ->label('Kategori')
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('status_label')
                        ->label('Durum')
                        ->badge()
                        ->color(fn (Tool $record) => match ($record->status) {
                            'available'   => 'success',
                            'loaned'      => 'warning',
                            'maintenance' => 'info',
                            'scrapped'    => 'danger',
                        }),

                    TextEntry::make('serial_no')
                        ->label('Seri Numarası')
                        ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                        ->copyable()
                        ->copyMessage('Kopyalandı!')
                        ->placeholder('—'),

                    TextEntry::make('barcode')
                        ->label('Barkod / QR Değeri')
                        ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                        ->copyable()
                        ->placeholder('—'),

                    TextEntry::make('location_label')
                        ->label('Konum'),

                    TextEntry::make('max_loan_days')
                        ->label('Maks. Ödünç Süresi')
                        ->suffix(' gün'),
                ]),

            // Sağ kolon — QR kod
            InfoSection::make('📱 QR Kod')
                ->columnSpan(['default' => 3, 'lg' => 1])
                ->schema([
                    ViewEntry::make('qr_preview')
                        ->label('')
                        ->view('filament.infolists.tool-qr-panel'),
                ]),

            // Fotoğraf (varsa)
            InfoSection::make('🖼️ Fotoğraf')
                ->columnSpan(['default' => 3])
                ->schema([
                    ImageEntry::make('image')
                        ->label('')
                        ->disk('public')
                        ->height(200)
                        ->extraImgAttributes(['class' => 'rounded-lg object-contain w-full max-h-60']),
                ])
                ->visible(fn (Tool $record) => !empty($record->image)),

            // Aktif zimmet
            InfoSection::make('📤 Şu Anki Zimmet')
                ->columnSpan(['default' => 3])
                ->columns(['default' => 1, 'sm' => 2, 'md' => 3])
                ->schema([
                    TextEntry::make('activeLoan.personnel.name')
                        ->label('Zimmetli Kişi')
                        ->placeholder('—'),
                    TextEntry::make('activeLoan.personnel.badge_number')
                        ->label('Sicil No')
                        ->placeholder('—'),
                    TextEntry::make('activeLoan.loaned_at')
                        ->label('Ödünç Alma')
                        ->dateTime('d.m.Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('activeLoan.planned_return_at')
                        ->label('Plan. İade')
                        ->dateTime('d.m.Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('activeLoan.status_label')
                        ->label('Zimmet Durumu')
                        ->badge()
                        ->color(fn (Tool $record) => match ($record->activeLoan?->status) {
                            'active'  => 'warning',
                            'overdue' => 'danger',
                            default   => 'gray',
                        })
                        ->placeholder('—'),
                ])
                ->visible(fn (Tool $record) => $record->activeLoan !== null),

            // Açıklama
            InfoSection::make('Açıklama')
                ->columnSpan(['default' => 3])
                ->schema([
                    TextEntry::make('description')
                        ->label('')
                        ->placeholder('Açıklama eklenmemiş.'),
                ])
                ->collapsible(),
        ])->columns(3);
    }


    // =========================================================================
    // TABLE (Liste)
    // =========================================================================

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(44)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=T&background=f97316&color=fff&size=44')
                    ->tooltip('🔍 Fotoğrafı büyütmek için tıklayın')
                    ->extraImgAttributes(['class' => 'cursor-pointer hover:opacity-85 hover:scale-105 transition shadow-sm'])
                    ->action(
                        Action::make('preview_image')
                            ->modalHeading(fn (Tool $record) => '📷 ' . $record->name)
                            ->modalDescription(fn (Tool $record) => ($record->serial_no ? 'Seri No: ' . $record->serial_no . ' • ' : '') . 'Konum: ' . ($record->location_label ?? '—'))
                            ->modalContent(fn (Tool $record) => view('filament.components.image-modal', ['record' => $record]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Kapat')
                    )
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Parça Adı')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Tool $record) => $record->category_label ?? ''),

                TextColumn::make('serial_no')
                    ->label('Seri No')
                    ->searchable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('status_label')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (Tool $record) => match ($record->status) {
                        'available'   => 'success',
                        'loaned'      => 'warning',
                        'maintenance' => 'info',
                        'scrapped'    => 'danger',
                        default       => 'gray',
                    })
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('status', $direction)),

                TextColumn::make('toolGroup.name')
                    ->label('Grup')
                    ->badge()
                    ->color(fn (Tool $record) => $record->toolGroup?->color ?: 'gray')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('toolroom.name')
                    ->label('Takımhane')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('location_label')
                    ->label('Konum')
                    ->placeholder('—'),

                TextColumn::make('activeLoan.personnel.name')
                    ->label('Zimmetli Kişi')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('max_loan_days')
                    ->label('Maks. Süre')
                    ->suffix(' gün')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Eklenme Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->since()                                      // "3 saat önce" gibi gösterim
                    ->tooltip(fn (Tool $record) => $record->created_at?->format('d.m.Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),  // varsayılan gizli
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // ── Eklenme Tarihi Filtresi ──────────────────────────────────
                SelectFilter::make('added_period')
                    ->label('🗓️ Eklenme Tarihi')
                    ->placeholder('Tümü')
                    ->options([
                        'today'     => '📅 Bugün Eklendi',
                        'yesterday' => '⬅️ Dün Eklendi',
                        'week'      => '📆 Bu Hafta Eklendi',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today'     => $query->whereDate('created_at', today())
                                                 ->orderByDesc('created_at'),
                            'yesterday' => $query->whereDate('created_at', today()->subDay())
                                                 ->orderByDesc('created_at'),
                            'week'      => $query->whereBetween('created_at', [
                                                     now()->startOfWeek(),
                                                     now()->endOfWeek(),
                                                 ])->orderByDesc('created_at'),
                            default     => $query,
                        };
                    }),

                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'available'   => 'Mevcut',
                        'loaned'      => 'Ödünçte',
                        'maintenance' => 'Bakımda',
                        'scrapped'    => 'Hurda',
                    ]),

                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(Tool::CATEGORIES),

                SelectFilter::make('toolroom_id')
                    ->label('🏢 Takımhane')
                    ->relationship('toolroom', 'name')
                    ->preload(),

                SelectFilter::make('tool_group_id')
                    ->label('🏷️ Araç / Parça Grubu')
                    ->relationship('toolGroup', 'name')
                    ->preload(),

                SelectFilter::make('slot_id')
                    ->label('📍 Göz / Konum')
                    ->relationship('slot', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_label)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('qr_label')
                        ->label('🔖 QR Etiket PDF')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->url(fn (Tool $record) => route('labels.tool.pdf', $record))
                        ->openUrlInNewTab(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_labels')
                        ->label('📦 Toplu Etiket PDF')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->action(fn (Collection $records) => redirect(
                            route('labels.tools.bulk', ['ids' => $records->pluck('id')->join(',')]))
                        )
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_excel')
                        ->label('📊 Seçilenleri Excel İndir')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(fn (Collection $records) => redirect(
                            route('export.tools.excel', ['ids' => $records->pluck('id')->join(',')]))
                        )
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_pdf')
                        ->label('📄 Seçilenleri PDF İndir (A4)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('danger')
                        ->action(fn (Collection $records) => redirect(
                            route('export.tools.pdf', ['ids' => $records->pluck('id')->join(',')]))
                        )
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTools::route('/'),
            'create' => Pages\CreateTool::route('/create'),
            'edit'   => Pages\EditTool::route('/{record}/edit'),
            'view'   => Pages\ViewTool::route('/{record}'),
        ];
    }
}
