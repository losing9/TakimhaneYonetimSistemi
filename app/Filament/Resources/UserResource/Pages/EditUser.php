<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $personnelId = $this->data['personnel_id'] ?? null;
        $user = $this->record;

        // Eski bağı kaldır
        \App\Models\Personnel::where('user_id', $user->id)->update(['user_id' => null]);

        if ($personnelId) {
            \App\Models\Personnel::where('id', $personnelId)->update(['user_id' => $user->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
