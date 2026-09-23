<?php

namespace App\Filament\Resources\ToolroomResource\Pages;

use App\Filament\Resources\ToolroomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToolroom extends CreateRecord
{
    protected static string $resource = ToolroomResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
