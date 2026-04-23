<?php

namespace App\Filament\Resources\CombinedResultResource\Pages;

use App\Filament\Resources\CombinedResultResource;
use App\Models\Standard;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClassCombinedReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = CombinedResultResource::class;

    protected string $view = 'filament.resources.combined-result-resource.pages.class-combined-report';

    public ?int $standardId = null;
    public array $data = [];

    public string $term = 'term_one';

    public int $year;

    public Collection $standards;

    public Collection $subjects;

    public ?string $standardName = null;

    public int $studentsCount = 0;

    public int $subjectsCount = 0;

    public int $resultsCount = 0;

    public ?float $averageScore = null;

    public ?float $passRate = null;

    public function mount(): void
    {
        $this->standards = Standard::query()->orderBy('name')->get();
        $this->subjects = Subject::query()->orderBy('name')->get();

        $this->term = (string) request()->query('term', 'term_one');
        $this->year = (int) request()->query('year', now()->year);

        $standardId = request()->query('standard_id');
        $this->standardId = $standardId !== null ? (int) $standardId : null;

        $this->form->fill([
            'standardId' => $this->standardId,
            'term' => $this->term,
            'year' => $this->year,
        ]);

        $this->updateMetrics();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('standardId')
                    ->label('Class')
                    ->options(fn () => Standard::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state): void {
                        $this->data['standardId'] = $state;
                        $this->standardId = $state !== null ? (int) $state : null;
                        $this->updateMetrics();
                        $this->resetPage();
                        $this->resetTable();
                    }),

                Select::make('term')
                    ->label('Term')
                    ->options([
                        'term_one' => 'Term One',
                        'term_two' => 'Term Two',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state): void {
                        $this->data['term'] = $state;
                        $this->term = (string) $state;
                        $this->updateMetrics();
                        $this->resetPage();
                        $this->resetTable();
                    }),

                TextInput::make('year')
                    ->label('Year')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->live()
                    ->afterStateUpdated(function ($state): void {
                        $this->data['year'] = $state;
                        $this->year = (int) $state;
                        $this->updateMetrics();
                        $this->resetTable();
                    }),
            ])
            ->columns(3)
            ->statePath('data');
    }

    protected function updateMetrics(): void
    {
        $this->standardName = null;
        $this->studentsCount = 0;
        $this->subjectsCount = (int) ($this->subjects?->count() ?? 0);
        $this->resultsCount = 0;
        $this->averageScore = null;
        $this->passRate = null;

                
        if (! $this->standardId) {
            return;
        }

        $this->standardName = $this->standards
            ->firstWhere('id', $this->standardId)
            ?->name;

        $students = Student::query()
            ->where('standard_id', $this->standardId)
            ->get();

        $this->studentsCount = (int) $students->count();

        $studentIds = $students->pluck('id');
        if ($studentIds->isEmpty()) {
            return;
        }

        $results = StudentResult::query()
            ->whereIn('student_id', $studentIds)
            ->where('term', $this->term)
            ->where('year', $this->year)
            ->get();

        $this->resultsCount = (int) $results->count();

        if ($this->resultsCount > 0) {
            $this->averageScore = round((float) $results->avg('score'), 1);
            $passes = (int) $results->filter(fn (StudentResult $r) => (int) $r->score >= 50)->count();
            $this->passRate = round(($passes / $this->resultsCount) * 100, 1);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([])
            ->actions([
                Action::make('student_result_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Student $record): string => route('filament.admin.students.result-report', $record, [
                        'term' => $this->term,
                        'year' => $this->year,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No students found')
            ->emptyStateDescription('Select a class to view student results.')
            ->paginated(false);
    }

    protected function getTableQuery(): Builder
    {
        if (! $this->standardId) {
            return Student::query()->whereRaw('1 = 0');
        }

        return Student::query()
            ->where('standard_id', $this->standardId)
            ->orderBy('admission_number');
    }

    protected function getTableColumns(): array
    {
        $columns = [
            TextColumn::make('admission_number')
                ->label('Admission No')
                ->sortable()
                ->searchable(),

            TextColumn::make('full_name')
                ->label('Student Name')
                ->getStateUsing(fn (Student $record): string => $record->first_name . ' ' . $record->last_name)
                ->sortable()
                ->searchable(),
        ];

        foreach ($this->subjects as $subject) {
            $columns[] = TextColumn::make("subject_{$subject->id}")
                ->label($subject->name)
                ->alignCenter()
                ->state(function (Student $record) use ($subject): ?int {
                    return $this->getStudentSubjectScore($record->id, $subject->id);
                })
                ->badge()
                ->color(function (Student $record) use ($subject): ?string {
                    $score = $this->getStudentSubjectScore($record->id, $subject->id);

                    if ($score === null) {
                        return 'gray';
                    }

                    if ($score >= 75) {
                        return 'success';
                    }

                    if ($score >= 50) {
                        return 'warning';
                    }

                    return 'danger';
                })
                ->formatStateUsing(function (?int $state): string {
                    return $state !== null ? (string) $state : '—';
                });
        }

        return $columns;
    }

    protected function getStudentSubjectScore(int $studentId, int $subjectId): ?int
    {
        if (! $this->standardId) {
            return null;
        }

        $result = StudentResult::query()
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('term', $this->term)
            ->where('year', $this->year)
            ->first();

        return $result?->score;
    }
}
