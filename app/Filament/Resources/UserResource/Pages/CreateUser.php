<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $personnelId = $this->data['personnel_id'] ?? null;
        $user = $this->record;

        if ($personnelId) {
            \App\Models\Personnel::where('id', $personnelId)->update(['user_id' => $user->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
