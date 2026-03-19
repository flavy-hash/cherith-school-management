<?php

namespace App\Filament\Teacher\Resources\StudentResultResource\Pages;

use App\Filament\Teacher\Resources\StudentResultResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentResult extends CreateRecord
{
    protected static string $resource = StudentResultResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['subject_id'] = $user?->teacherSubject?->subject_id;
        $data['teacher_id'] = $user?->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
