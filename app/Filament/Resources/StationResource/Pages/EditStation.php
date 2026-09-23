<?php
namespace App\Filament\Resources\StationResource\Pages;
use App\Filament\Resources\StationResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditStation extends EditRecord
{
    protected static string $resource = StationResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
