<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_today')
                ->label('📊 Bugünü Arşivle')
                ->color('warning')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->action(function () {
                    \Artisan::call('reports:generate-daily', ['date' => now()->toDateString()]);
                    Notification::make()
                        ->title('Bugünün raporu oluşturuldu!')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Bugünü Arşivle')
                ->modalDescription('Bugünün tüm zimmet kayıtları Excel ve PDF olarak arşivlenecek.'),
        ];
    }
}
