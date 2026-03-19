<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\StudentResource\Pages\ListStudents;
use App\Models\Student;
use App\Models\StudentResult;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Results';

    protected static ?string $navigationLabel = 'Enter Scores';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $standardId = $user?->teacherSubject?->standard_id;

        return parent::getEloquentQuery()
            ->with(['standard'])
            ->when($standardId, fn (Builder $q) => $q->where('standard_id', $standardId));
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
                    ->form(function (Student $record): array {
                        $user = auth()->user();
                        $subjectId = $user?->teacherSubject?->subject_id;

                        $term = request()->query('term', 'term_one');
                        $year = (int) request()->query('year', date('Y'));

                        $existingScore = null;
                        if ($subjectId) {
                            $existingScore = StudentResult::query()
                                ->where('student_id', $record->id)
                                ->where('subject_id', $subjectId)
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
                        $user = auth()->user();
                        $subjectId = $user?->teacherSubject?->subject_id;

                        if (! $subjectId) {
                            abort(403, 'You are not assigned to any subject.');
                        }

                        StudentResult::updateOrCreate(
                            [
                                'student_id' => $record->id,
                                'subject_id' => $subjectId,
                                'term' => $data['term'],
                                'year' => (int) $data['year'],
                            ],
                            [
                                'teacher_id' => $user->id,
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

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
        ];
    }
}
