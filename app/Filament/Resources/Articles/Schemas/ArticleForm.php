<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('title.' . config('app.fallback_locale', 'en'))
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('content.' . config('app.fallback_locale', 'en'))
                    ->label('Content')
                    ->required(),

                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->required(),
            ]);
    }
}
