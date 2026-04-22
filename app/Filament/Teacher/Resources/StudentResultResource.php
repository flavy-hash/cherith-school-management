<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\StudentResultResource\Pages\CreateStudentResult;
use App\Filament\Teacher\Resources\StudentResultResource\Pages\EditStudentResult;
use App\Filament\Teacher\Resources\StudentResultResource\Pages\ListStudentResults;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\TeacherSubject;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class StudentResultResource extends Resource
{
    protected static ?string $model = StudentResult::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Results';

    protected static ?string $navigationLabel = 'Student Results';

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();
        $assignmentId = Session::get('teacher_active_assignment_id');
        $assignment = $assignmentId ? TeacherSubject::find($assignmentId) : null;

        if (! $assignment && $userId) {
            $assignment = TeacherSubject::query()
                ->where('user_id', $userId)
                ->orderBy('id')
                ->first();

            if ($assignment) {
                Session::put('teacher_active_assignment_id', (int) $assignment->id);
            }
        }

        return parent::getEloquentQuery()
            ->when($assignment, fn (Builder $q) => $q->where('subject_id', $assignment->subject_id));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Student')
                    ->options(fn () => Student::query()->orderBy('first_name')->orderBy('last_name')->get()->mapWithKeys(fn (Student $s) => [$s->id => $s->full_name])->all())
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('term')
                    ->options([
                        'term_one' => 'Term One',
                        'term_two' => 'Term Two',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->required()
                    ->default((int) date('Y')),

                Forms\Components\TextInput::make('score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.admission_number')
                    ->label('Admission #')
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Student')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.standard.name')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('term')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'term_two' ? 'Term Two' : 'Term One'),

                Tables\Columns\TextColumn::make('year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('score')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->since()
                    ->label('Updated'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        'term_one' => 'Term One',
                        'term_two' => 'Term Two',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentResults::route('/'),
            'create' => CreateStudentResult::route('/create'),
            'edit' => EditStudentResult::route('/{record}/edit'),
        ];
    }
}
