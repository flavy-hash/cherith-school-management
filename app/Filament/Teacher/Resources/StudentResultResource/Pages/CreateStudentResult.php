<?php

namespace App\Filament\Teacher\Resources\StudentResultResource\Pages;

use App\Filament\Teacher\Resources\StudentResultResource;
use App\Models\TeacherSubject;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateStudentResult extends CreateRecord
{
    protected static string $resource = StudentResultResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $assignmentId = Session::get('teacher_active_assignment_id');
        $assignment = $assignmentId ? TeacherSubject::find($assignmentId) : null;

        if (! $assignment && auth()->id()) {
            $assignment = TeacherSubject::query()
                ->where('user_id', auth()->id())
                ->orderBy('id')
                ->first();

            if ($assignment) {
                Session::put('teacher_active_assignment_id', (int) $assignment->id);
            }
        }

        if (! $assignment) {
            abort(403, 'Please select a class and subject assignment first.');
        }

        $data['subject_id'] = $assignment->subject_id;
        $data['teacher_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
