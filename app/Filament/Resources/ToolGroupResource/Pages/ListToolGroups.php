<?php

namespace App\Filament\Resources\ToolGroupResource\Pages;

use App\Filament\Resources\ToolGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListToolGroups extends ListRecords
{
    protected static string $resource = ToolGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('➕ Yeni Parça Grubu Ekle'),
        ];
    }
}
