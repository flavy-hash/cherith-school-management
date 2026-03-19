<?php

namespace App\Filament\Resources\StandardResource\Pages;

use App\Filament\Resources\StandardResource;
use Filament\Resources\Pages\EditRecord;

class EditStandard extends EditRecord
{
    protected static string $resource = StandardResource::class;

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
