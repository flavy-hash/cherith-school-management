<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Illuminate\Support\Str;

class TranslatableText extends Component
{
    protected string $view = 'filament.forms.components.translatable-text';

    protected string $name;

    protected array $locales = [];

    protected ?string $label = null;

    protected bool $required = false;

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function locales(array $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocales(): array
    {
        return empty($this->locales) ? config('translatable.locales', ['en']) : $this->locales;
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::title(str_replace('_', ' ', $this->name));
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getFields(): array
    {
        $fields = [];
        
        foreach ($this->getLocales() as $locale) {
            $fields[] = TextInput::make("{$this->name}.{$locale}")
                ->label("{$this->getLabel()} ({$locale})")
                ->required($this->required && $locale === config('app.fallback_locale', 'en'))
                ->maxLength(255);
        }

        return $fields;
    }
}
