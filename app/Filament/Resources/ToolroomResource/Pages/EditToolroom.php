<?php

namespace App\Filament\Resources\ToolroomResource\Pages;

use App\Filament\Resources\ToolroomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditToolroom extends EditRecord
{
    protected static string $resource = ToolroomResource::class;

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
