<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\TeacherSubject;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class AssignedClassesWidget extends Widget
{
    protected string $view = 'filament.teacher.widgets.assigned-classes-widget';

    public function getAssignments(): Collection
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        return TeacherSubject::query()
            ->where('user_id', $userId)
            ->with(['standard', 'subject'])
            ->orderBy('standard_id')
            ->orderBy('subject_id')
            ->get();
    }
}
