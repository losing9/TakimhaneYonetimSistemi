<?php

namespace App\Filament\Resources\ToolGroupResource\Pages;

use App\Filament\Resources\ToolGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToolGroup extends CreateRecord
{
    protected static string $resource = ToolGroupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
