<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Filament\Resources\TeacherAssignmentResource;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;

class ListTeacherAssignments extends ListRecords
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('addTeacher')
                ->label('Add Teacher')
                ->icon('heroicon-o-user-plus')
                ->form([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('password')
                        ->password()
                        ->required()
                        ->minLength(6),
                    Select::make('subject_id')
                        ->label('Subject')
                        ->options(fn () => Subject::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('standard_id')
                        ->label('Class')
                        ->options(fn () => Standard::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    $teacher = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                    ]);

                    TeacherSubject::updateOrCreate(
                        ['user_id' => $teacher->id],
                        [
                            'subject_id' => (int) $data['subject_id'],
                            'standard_id' => $data['standard_id'] ? (int) $data['standard_id'] : null,
                        ],
                    );
                }),
        ];
    }
}
