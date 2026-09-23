<?php

namespace App\Filament\Resources\ToolroomResource\Pages;

use App\Filament\Resources\ToolroomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListToolrooms extends ListRecords
{
    protected static string $resource = ToolroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('➕ Yeni Takımhane Ekle'),
        ];
    }
}
