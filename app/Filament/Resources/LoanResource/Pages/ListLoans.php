<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Yeni Zimmet Ver'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Tüm Zimmetler'),
            
            'self_service' => \Filament\Resources\Components\Tab::make('Self-Service')
                ->modifyQueryUsing(fn ($query) => $query->whereNotNull('loaned_by_user_id')->orWhereNotNull('return_station_id'))
                ->icon('heroicon-o-device-phone-mobile'),
                
            'active' => \Filament\Resources\Components\Tab::make('Ödünçte')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', ['active', 'overdue']))
                ->icon('heroicon-o-clock'),
                
            'overdue' => \Filament\Resources\Components\Tab::make('Gecikenler')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'overdue'))
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
