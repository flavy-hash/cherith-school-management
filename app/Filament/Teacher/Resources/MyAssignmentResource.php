<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\MyAssignmentResource\Pages\ListMyAssignments;
use App\Models\TeacherSubject;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyAssignmentResource extends Resource
{
    protected static ?string $model = TeacherSubject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Results';

    protected static ?string $navigationLabel = 'My Classes';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('standard.name')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->actions([
                Action::make('enterScores')
                    ->label('Enter Scores')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (TeacherSubject $record): string => url('/teacher/students?assignment_id=' . $record->id)),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        return parent::getEloquentQuery()
            ->with(['standard', 'subject'])
            ->when($userId, fn (Builder $q) => $q->where('user_id', $userId));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyAssignments::route('/'),
        ];
    }
}
