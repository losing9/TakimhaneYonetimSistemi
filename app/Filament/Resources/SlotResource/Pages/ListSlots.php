<?php

namespace App\Filament\Resources\SlotResource\Pages;

use App\Filament\Resources\SlotResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlots extends ListRecords
{
    protected static string $resource = SlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan_qr')
                ->label('📷 QR Okut & Gözü Aç')
                ->icon('heroicon-o-qr-code')
                ->color('warning')
                ->modalHeading('📷 Göz QR Kodu Okut')
                ->modalContent(fn () => view('filament.components.slot-qr-scanner-modal'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Kapat')
                ->modalWidth('md'),

            CreateAction::make(),
        ];
    }
}
