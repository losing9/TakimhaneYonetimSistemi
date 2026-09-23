<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportResource\Pages;
use App\Models\DailyReport;
use App\Services\ReportService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class DailyReportResource extends Resource
{
    protected static ?string $model          = DailyReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Zimmet İşlemleri';
    protected static ?string $navigationLabel = 'Günlük Arşiv';
    protected static ?string $modelLabel      = 'Günlük Rapor';
    protected static ?string $pluralModelLabel = 'Günlük Raporlar';
    protected static ?int    $navigationSort  = 3;

    // Yeni kayıt formu yok — sadece liste ve export
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')
                    ->label('Tarih')
                    ->date('d.m.Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_loans')
                    ->label('Zimmet')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('total_returns')
                    ->label('İade')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('overdue_count')
                    ->label('Gecikmiş')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->alignCenter(),

                TextColumn::make('new_tools')
                    ->label('Yeni Parça')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                IconColumn::make('file_path_xlsx')
                    ->label('Excel')
                    ->boolean()
                    ->getStateUsing(fn (DailyReport $r) => $r->hasXlsx())
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),

                IconColumn::make('file_path_pdf')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn (DailyReport $r) => $r->hasPdf())
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('danger')
                    ->falseColor('gray'),

                TextColumn::make('generated_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                SelectFilter::make('year')
                    ->label('Yıl')
                    ->options(fn () => collect(range(now()->year, 2020))
                        ->mapWithKeys(fn ($y) => [$y => (string) $y])
                        ->toArray()
                    )
                    ->query(fn ($query, $data) => $data['value']
                        ? $query->whereYear('report_date', $data['value'])
                        : $query
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('download_pdf')
                        ->label('PDF İndir')
                        ->icon('heroicon-o-document')
                        ->color('danger')
                        ->visible(fn (DailyReport $r) => $r->hasPdf())
                        ->url(fn (DailyReport $r) => route('export.report', [
                            'from'   => $r->report_date->toDateString(),
                            'to'     => $r->report_date->toDateString(),
                            'format' => 'pdf',
                            'title'  => $r->formatted_date . ' Günlük Rapor',
                        ]))
                        ->openUrlInNewTab(),

                    Action::make('download_xlsx')
                        ->label('Excel İndir')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->visible(fn (DailyReport $r) => $r->hasXlsx())
                        ->url(fn (DailyReport $r) => route('export.report', [
                            'from'   => $r->report_date->toDateString(),
                            'to'     => $r->report_date->toDateString(),
                            'format' => 'xlsx',
                        ]))
                        ->openUrlInNewTab(),

                    // Yoksa retroaktif oluştur
                    Action::make('regenerate')
                        ->label('Yeniden Oluştur')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (DailyReport $record) {
                            app(ReportService::class)->archiveDay(
                                Carbon::parse($record->report_date)
                            );
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Raporu Yeniden Oluştur')
                        ->modalDescription('Bu günün Excel ve PDF raporu yeniden oluşturulacak. Devam?'),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('Arşivlenmiş rapor bulunamadı')
            ->emptyStateDescription('Raporlar otomatik olarak her gece 23:55\'te oluşturulur. "reports:generate-daily" komutuyla manuel olarak da çalıştırabilirsiniz.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Raporlar sadece otomatik/komutla oluşturulur
    }
}
