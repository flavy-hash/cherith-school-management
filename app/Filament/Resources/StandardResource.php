<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StandardResource\Pages\CreateStandard;
use App\Filament\Resources\StandardResource\Pages\EditStandard;
use App\Filament\Resources\StandardResource\Pages\ListStandards;
use App\Models\Standard;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StandardResource extends Resource
{
    protected static ?string $model = Standard::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'School Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('STD 1'),
                
                Forms\Components\TextInput::make('class_teacher')
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('term_one_fee')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),
                
                Forms\Components\TextInput::make('term_two_fee')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),

                Forms\Components\TextInput::make('expected_students')
                    ->label('Expected Students')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('class_teacher')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('term_one_fee')
                    ->money('TSH')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('term_two_fee')
                    ->money('TSH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_students')
                    ->label('Expected')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students'),
            ])
            ->filters([])
            ->actions([
                \Filament\Actions\Action::make('students')
                    ->label('Students')
                    ->icon('heroicon-o-user-group')
                    ->url(fn (Standard $record) => StudentResource::getUrl('index', ['standard_id' => $record->id])),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStandards::route('/'),
            'create' => CreateStandard::route('/create'),
            'edit' => EditStandard::route('/{record}/edit'),
        ];
    }
}
