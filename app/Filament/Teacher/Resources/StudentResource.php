<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\StudentResource\Pages\ListStudents;
use App\Models\Standard;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Results';

    protected static ?string $navigationLabel = 'Enter Scores';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $assignmentId = Session::get('teacher_active_assignment_id');

        $assignment = $assignmentId ? TeacherSubject::find($assignmentId) : null;

        return parent::getEloquentQuery()
            ->with(['standard'])
            ->when($assignment, function (Builder $q, TeacherSubject $assignment) {
                $q->whereHas('studentSubjects', function (Builder $sub) use ($assignment) {
                    $sub->where('subject_id', $assignment->subject_id)
                        ->when($assignment->standard_id, fn (Builder $sq) => $sq->where('standard_id', $assignment->standard_id));
                });
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admission_number')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Student')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('standard.name')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('standard')
                    ->relationship('standard', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('score')
                    ->label('Enter / Update Score')
                    ->icon('heroicon-o-pencil-square')
                    ->form(function (Student $record) {
                        $assignment = static::getActiveAssignment();

                        $term = now()->quarter <= 4 ? 'term_one' : 'term_two';
                        $year = now()->year;

                        $existingScore = null;
                        if ($assignment) {
                            $existingScore = StudentResult::query()
                                ->where('student_id', $record->id)
                                ->where('subject_id', $assignment->subject_id)
                                ->where('term', $term)
                                ->where('year', $year)
                                ->value('score');
                        }

                        return [
                            Forms\Components\Select::make('term')
                                ->options([
                                    'term_one' => 'Term One',
                                    'term_two' => 'Term Two',
                                ])
                                ->required()
                                ->default($term),

                            Forms\Components\TextInput::make('year')
                                ->numeric()
                                ->minValue(2000)
                                ->maxValue(2100)
                                ->required()
                                ->default($year),

                            Forms\Components\TextInput::make('score')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->required()
                                ->default($existingScore),
                        ];
                    })
                    ->action(function (Student $record, array $data): void {
                        $assignment = static::getActiveAssignment();

                        if (! $assignment) {
                            Notification::make()
                                ->title('No assignment selected')
                                ->body('Please select a class and subject first.')
                                ->warning()
                                ->send();
                            return;
                        }

                        StudentResult::updateOrCreate(
                            [
                                'student_id' => $record->id,
                                'subject_id' => $assignment->subject_id,
                                'term' => $data['term'],
                                'year' => (int) $data['year'],
                            ],
                            [
                                'teacher_id' => auth()->id(),
                                'score' => (int) $data['score'],
                            ],
                        );

                        Notification::make()
                            ->title('Score saved')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    protected static function getActiveAssignment(): ?TeacherSubject
    {
        $assignmentId = Session::get('teacher_active_assignment_id');
        return $assignmentId ? TeacherSubject::find($assignmentId) : null;
    }

    public static function getHeaderActions(): array
    {
        $user = auth()->user();
        $assignments = TeacherSubject::where('user_id', $user->id)
            ->with(['subject', 'standard'])
            ->get();

        if ($assignments->isEmpty()) {
            return [];
        }

        $active = static::getActiveAssignment();
        $label = $active
            ? "Class: " . ($active->standard?->name ?? 'Any') . " — Subject: " . $active->subject->name
            : 'Select Class & Subject';

        return [
            Action::make('selectAssignment')
                ->label($label)
                ->icon('heroicon-o-academic-cap')
                ->form([
                    Forms\Components\Select::make('assignment_id')
                        ->label('Choose your assignment')
                        ->options(
                            $assignments->mapWithKeys(fn (TeacherSubject $a) => [
                                $a->id => ($a->standard?->name ?? 'Any Class') . ' — ' . $a->subject->name,
                            ])
                        )
                        ->required()
                        ->default($active?->id),
                ])
                ->action(function (array $data): void {
                    Session::put('teacher_active_assignment_id', (int) $data['assignment_id']);
                    Notification::make()
                        ->title('Assignment selected')
                        ->success()
                        ->send();
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
        ];
    }
}
