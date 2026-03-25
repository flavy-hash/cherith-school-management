<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CombinedResultResource\Pages\ListCombinedResults;
use App\Models\Standard;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CombinedResultResource extends Resource
{
    protected static ?string $model = StudentResult::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Analytics';

    protected static ?string $navigationLabel = 'Combined Results';

    protected static ?string $modelLabel = 'Result';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->columns([
                Tables\Columns\TextColumn::make('student.admission_number')
                    ->label('Admission No')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Student')
                    ->getStateUsing(fn (StudentResult $record): string => $record->student->first_name . ' ' . $record->student->last_name)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.standard.name')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable(),

                Tables\Columns\TextColumn::make('term')
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('standard_id')
                    ->label('Class')
                    ->relationship('student.standard', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->options(fn () => Subject::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        '1' => 'Term 1',
                        '2' => 'Term 2',
                        '3' => 'Term 3',
                    ]),

                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => StudentResult::query()->distinct()->pluck('year')->sort()->mapWithKeys(fn ($year) => [$year => $year])->all()),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('students', 'student_results.student_id', '=', 'students.id')
            ->join('standards', 'students.standard_id', '=', 'standards.id')
            ->join('subjects', 'student_results.subject_id', '=', 'subjects.id')
            ->select('student_results.*')
            ->with(['student.standard', 'subject'])
            ->orderBy('standards.name')
            ->orderBy('students.admission_number')
            ->orderBy('subjects.name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCombinedResults::route('/'),
        ];
    }
}
