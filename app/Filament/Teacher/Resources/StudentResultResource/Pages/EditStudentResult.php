<?php

namespace App\Filament\Teacher\Resources\StudentResultResource\Pages;

use App\Filament\Teacher\Resources\StudentResultResource;
use Filament\Resources\Pages\EditRecord;

class EditStudentResult extends EditRecord
{
    protected static string $resource = StudentResultResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['subject_id'], $data['teacher_id']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
