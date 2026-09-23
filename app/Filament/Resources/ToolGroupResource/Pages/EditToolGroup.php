<?php

namespace App\Filament\Resources\ToolGroupResource\Pages;

use App\Filament\Resources\ToolGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditToolGroup extends EditRecord
{
    protected static string $resource = ToolGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
