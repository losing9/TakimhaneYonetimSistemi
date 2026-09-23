<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\Tool;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTools    = Tool::count();
        $availableTools= Tool::available()->count();
        $loanedTools   = Tool::where('status', 'loaned')->count();
        $overdueLoans  = Loan::overdue()->count();
        $todayLoans    = Loan::today()->count();
        $maintenanceTools = Tool::where('status', 'maintenance')->count();

        return [
            Stat::make('Toplam Parça', $totalTools)
                ->description('Sistemdeki tüm parçalar')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('gray'),

            Stat::make('Mevcut Parçalar', $availableTools)
                ->description('Rafta bekleyen parçalar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Ödünçte', $loanedTools)
                ->description('Şu an dışarıda olan parçalar')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),

            Stat::make('🚨 Gecikmiş', $overdueLoans)
                ->description('İade süresi geçen zimmetler')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueLoans > 0 ? 'danger' : 'success'),

            Stat::make('Bugün Verilen', $todayLoans)
                ->description('Bugünkü zimmet işlemleri')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Bakımda', $maintenanceTools)
                ->description('Bakım / Onarımdaki parçalar')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),
        ];
    }
}
