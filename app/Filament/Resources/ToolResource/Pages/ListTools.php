<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTools extends ListRecords
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('📊 Excel İndir (.xlsx)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->tooltip('Tüm parça ve envanter listesini Excel olarak indirir (Yedek)')
                ->url(fn () => route('export.tools.excel'))
                ->openUrlInNewTab(),

            Action::make('export_pdf')
                ->label('📄 PDF İndir (A4)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->tooltip('Tüm parça ve envanter listesini A4 yatay PDF olarak indirir')
                ->url(fn () => route('export.tools.pdf'))
                ->openUrlInNewTab(),

            CreateAction::make(),
        ];
    }
}
