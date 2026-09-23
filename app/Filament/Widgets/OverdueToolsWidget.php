<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueToolsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = '🚨 Gecikmiş Zimmetler';

    public static function canView(): bool
    {
        $q = Loan::overdue();
        if ($roomId = auth()->user()?->toolroom_id) {
            $q->where('toolroom_id', $roomId);
        }
        return $q->exists();
    }

    public function table(Table $table): Table
    {
        $query = Loan::query()
            ->overdue()
            ->with(['tool', 'personnel'])
            ->orderBy('planned_return_at');

        if ($roomId = auth()->user()?->toolroom_id) {
            $query->where('toolroom_id', $roomId);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('tool.name')
                    ->label('Parça')
                    ->searchable(),

                TextColumn::make('personnel.name')
                    ->label('Zimmetli Kişi')
                    ->searchable(),

                TextColumn::make('personnel.badge_number')
                    ->label('Sicil No')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('personnel.department')
                    ->label('Departman')
                    ->placeholder('—'),

                TextColumn::make('planned_return_at')
                    ->label('İade Edilmeliydi')
                    ->dateTime('d.m.Y H:i')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('overdue_days')
                    ->label('Gecikme')
                    ->suffix(' gün')
                    ->badge()
                    ->color('danger'),
            ])
            ->striped()
            ->paginated([5, 10, 25]);
    }
}
