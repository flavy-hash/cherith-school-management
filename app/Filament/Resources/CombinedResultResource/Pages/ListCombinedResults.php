<?php

namespace App\Filament\Resources\CombinedResultResource\Pages;

use App\Filament\Resources\CombinedResultResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCombinedResults extends ListRecords
{
    protected static string $resource = CombinedResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('class_report')
                ->label('Class Report')
                ->icon('heroicon-o-table-cells')
                ->url(CombinedResultResource::getUrl('class-report')),
        ];
    }
}
