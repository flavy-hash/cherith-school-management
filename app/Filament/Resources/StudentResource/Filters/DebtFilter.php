<?php

namespace App\Filament\Resources\StudentResource\Filters;

use Filament\Forms;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DebtFilter extends Filter
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->form([
            Forms\Components\Select::make('debt_level')
                ->options([
                    'high' => 'High Debt (> 10,000)',
                    'medium' => 'Medium Debt (5,000 - 10,000)',
                    'low' => 'Low Debt (1,000 - 5,000)',
                    'none' => 'No Debt',
                ])
        ]);
        
        $this->query(function (Builder $query, array $data) {
            return $query->when($data['debt_level'] ?? null, function ($query, $level) {
                switch ($level) {
                    case 'high':
                        return $query->where('total_debt', '>', 10000);
                    case 'medium':
                        return $query->whereBetween('total_debt', [5000, 10000]);
                    case 'low':
                        return $query->whereBetween('total_debt', [1000, 5000]);
                    case 'none':
                        return $query->where('total_debt', '<=', 0);
                }
            });
        });
    }
}
