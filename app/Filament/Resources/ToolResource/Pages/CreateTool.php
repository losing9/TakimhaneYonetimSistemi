<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTool extends CreateRecord
{
    protected static string $resource = ToolResource::class;

    /**
     * Kayıt sonrası detay sayfasına git — kullanıcı QR kodunu görsün
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        $serial = $this->getRecord()->serial_no;
        return "Parça oluşturuldu — Seri No: {$serial}";
    }
}
