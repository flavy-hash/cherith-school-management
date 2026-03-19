<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages\CreatePayment;
use App\Filament\Resources\PaymentResource\Pages\EditPayment;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Payment;
use App\Models\Student;
use App\Services\TraEfdService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Financial Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Student $record): string => $record->full_name)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if (! $state) {
                            return;
                        }

                        $student = Student::find($state);

                        if (! $student) {
                            return;
                        }

                        $set('balance', $student->total_debt);
                    }),

                Forms\Components\Select::make('term')
                    ->options([
                        'term_one' => 'Term One',
                        'term_two' => 'Term Two',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('year')
                    ->required()
                    ->numeric()
                    ->default(date('Y')),

                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('TSH'),

                Forms\Components\TextInput::make('balance')
                    ->label('Remaining Balance')
                    ->numeric()
                    ->prefix('TSH')
                    ->disabled(),

                Forms\Components\Select::make('payment_method')
                    ->options([
                        'bank_transfer' => 'CRDB',
                        'mobile_money' => 'NMB',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('transaction_id')
                    ->label('Transaction ID/Reference')
                    ->maxLength(255),

                Forms\Components\DatePicker::make('payment_date')
                    ->required()
                    ->default(now()),

                Forms\Components\Textarea::make('notes')
                    ->rows(3),

                Forms\Components\Toggle::make('is_verified')
                    ->label('Verify Payment')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark')
                    ->reactive(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Student')
                    ->formatStateUsing(fn ($record) => $record->student->full_name)
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('student.standard.name')
                    ->label('Class')
                    ->sortable(),

                Tables\Columns\TextColumn::make('term')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->badge(),

                Tables\Columns\TextColumn::make('year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('TSH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance')
                    ->money('TSH')
                    ->color(fn ($record) => $record->balance > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'bank_transfer' => 'CRDB',
                        'mobile_money' => 'NMB',
                        'cash' => 'Cash',
                        default => ucwords(str_replace('_', ' ', (string) $state)),
                    }),

                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified')
                    ->action(function (Payment $record): void {
                        $record->update([
                            'is_verified' => ! $record->is_verified,
                            'verified_at' => $record->is_verified ? null : now(),
                            'verified_by' => $record->is_verified ? null : auth()->id(),
                        ]);
                    }),

                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        'term_one' => 'Term One',
                        'term_two' => 'Term Two',
                    ]),

                Tables\Filters\SelectFilter::make('tra_status')
                    ->label('TRA Status')
                    ->options([
                        'sent' => 'Sent to TRA',
                        'not_sent' => 'Not sent',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $query
                            ->when($value === 'sent', fn (Builder $q) => $q->whereNotNull('tra_receipt_synced_at'))
                            ->when($value === 'not_sent', fn (Builder $q) => $q->whereNull('tra_receipt_synced_at'));
                    }),

                Tables\Filters\SelectFilter::make('is_verified')
                    ->label('Verification Status')
                    ->options([
                        '1' => 'Verified',
                        '0' => 'Not Verified',
                    ]),

                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q) => $q->whereDate('payment_date', '>=', $data['from']))
                            ->when($data['to'] ?? null, fn (Builder $q) => $q->whereDate('payment_date', '<=', $data['to']));
                    }),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('tra_sent_payments')
                    ->label('TRA Sent Payments')
                    ->icon('heroicon-o-funnel')
                    ->color('info')
                    ->url(fn () => static::getUrl('index', [
                        'tableFilters' => [
                            'tra_status' => ['value' => 'sent'],
                        ],
                    ])),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\Action::make('sync_tra_efd_receipt')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function (Payment $record): void {
                            $receipt = app(TraEfdService::class)->syncReceipt($record, true);

                            if (! $receipt) {
                                Notification::make()
                                    ->title('TRA EFD Receipt')
                                    ->body('Failed to fetch receipt from TRA. Please check configuration / connectivity and try again.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('TRA EFD Receipt Synced')
                                ->body('Receipt has been fetched and stored on this payment.')
                                ->success()
                                ->send();
                        }),
                    \Filament\Actions\Action::make('verify')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Payment $record): void {
                            $record->update([
                                'is_verified' => true,
                                'verified_at' => now(),
                                'verified_by' => auth()->id(),
                            ]);
                        })
                        ->hidden(fn (Payment $record) => $record->is_verified),

                    \Filament\Actions\Action::make('unverify')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Payment $record): void {
                            $record->update([
                                'is_verified' => false,
                                'verified_at' => null,
                                'verified_by' => null,
                            ]);
                        })
                        ->visible(fn (Payment $record) => $record->is_verified),

                    \Filament\Actions\Action::make('print_receipt')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->url(fn (Payment $record) => route('filament.admin.payments.receipt', $record))
                        ->openUrlInNewTab(),

                    \Filament\Actions\Action::make('print_receipt_80mm')
                        ->label('Print Receipt (80mm)')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn (Payment $record) => route('filament.admin.payments.receipt', $record, ['format' => '80mm']))
                        ->openUrlInNewTab(),

                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                \Filament\Actions\BulkAction::make('print_selected_receipts')
                    ->label('Print Selected Receipts')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->action(function ($records) {
                        if ($records->isEmpty()) {
                            Notification::make()
                                ->title('No Selection')
                                ->body('Please select at least one payment to print receipts.')
                                ->warning()
                                ->send();

                            return;
                        }

                        return redirect()->away(route('filament.admin.payments.receipts.bulk', [
                            'ids' => $records->pluck('id')->implode(','),
                        ]));
                    }),

                \Filament\Actions\BulkAction::make('print_selected_receipts_80mm')
                    ->label('Print Selected Receipts (80mm)')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->action(function ($records) {
                        if ($records->isEmpty()) {
                            Notification::make()
                                ->title('No Selection')
                                ->body('Please select at least one payment to print receipts.')
                                ->warning()
                                ->send();

                            return;
                        }

                        return redirect()->away(route('filament.admin.payments.receipts.bulk', [
                            'ids' => $records->pluck('id')->implode(','),
                            'format' => '80mm',
                        ]));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
