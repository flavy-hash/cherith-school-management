<?php

namespace App\Filament\Teacher\Resources\StudentResource\Pages;

use App\Filament\Teacher\Resources\StudentResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Session;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    public function mount(): void
    {
        parent::mount();

        $assignmentId = request()->query('assignment_id');
        if ($assignmentId) {
            Session::put('teacher_active_assignment_id', (int) $assignmentId);
        }
    }
}
