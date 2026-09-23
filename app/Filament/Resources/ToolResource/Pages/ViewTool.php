<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use App\Models\Tool;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTool extends ViewRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // PDF etiket indirme butonu — her zaman görünür
            Action::make('qr_label_pdf')
                ->label('🔖 Etiket PDF İndir')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->url(fn () => route('labels.tool.pdf', $this->getRecord()))
                ->openUrlInNewTab(),

            EditAction::make(),
        ];
    }
}
