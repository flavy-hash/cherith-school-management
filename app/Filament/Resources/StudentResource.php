<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages\CreateStudent;
use App\Filament\Resources\StudentResource\Pages\EditStudent;
use App\Filament\Resources\StudentResource\Pages\ListStudents;
use App\Filament\Resources\StudentResource\Pages\StudentDebtReport;
use App\Models\Student;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Student Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('standard_id')
                    ->relationship('standard', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('admission_number')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Auto-generated')
                    ->helperText('Generated after saving based on class and student initials.')
                    ->maxLength(255),

                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('date_of_birth')
                    ->required(),

                Forms\Components\TextInput::make('parent_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('parent_phone')
                    ->required()
                    ->tel()
                    ->maxLength(20),

                Forms\Components\TextInput::make('parent_email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admission_number')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Student Name')
                    ->sortable(['first_name', 'last_name'])
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('standard.name')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('parent_phone')
                    ->label('Parent Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('term_one_balance')
                    ->label('Term 1 Balance')
                    ->money('TSH')
                    ->color(fn ($record) => $record->term_one_balance > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('term_two_balance')
                    ->label('Term 2 Balance')
                    ->money('TSH')
                    ->color(fn ($record) => $record->term_two_balance > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('total_debt')
                    ->label('Total Debt')
                    ->money('TSH')
                    ->color(fn ($record) => $record->total_debt > 0 ? 'danger' : 'success'),

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
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\Action::make('payments')
                        ->icon('heroicon-o-currency-dollar')
                        ->url(fn (Student $record) => PaymentResource::getUrl('index', ['student_id' => $record->id])),
                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // Keep empty until bulk action classes are confirmed in this Filament v4 build.
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
            'debt-report' => StudentDebtReport::route('/debt-report'),
        ];
    }
}
