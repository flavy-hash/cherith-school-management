<x-filament-panels::page>
    <x-filament-panels::header
        :actions="$this->getCachedHeaderActions()"
        :heading="$this->getHeading()"
        :subheading="$this->getSubheading()"
    >
        <x-slot name="heading">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-full bg-primary-500 flex items-center justify-center">
                    <span class="text-white font-bold text-lg">CJS</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Cherith Junior School
                    </h1>
                    <p class="text-sm text-gray-500">
                        Student Management & Financial Tracking System
                    </p>
                </div>
            </div>
        </x-slot>
    </x-filament-panels::header>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('filament.admin.resources.students.create') }}" 
                   class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-primary-100 p-2">
                            <x-heroicon-o-user-plus class="h-5 w-5 text-primary-600" />
                        </div>
                        <span class="font-medium text-gray-900">Add New Student</span>
                    </div>
                    <x-heroicon-o-arrow-right class="h-5 w-5 text-gray-400" />
                </a>
                
                <a href="{{ route('filament.admin.resources.payments.create') }}" 
                   class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2">
                            <x-heroicon-o-currency-dollar class="h-5 w-5 text-green-600" />
                        </div>
                        <span class="font-medium text-gray-900">Record Payment</span>
                    </div>
                    <x-heroicon-o-arrow-right class="h-5 w-5 text-gray-400" />
                </a>
                
                <a href="{{ route('filament.admin.resources.payments.index', ['tableFilters' => ['is_verified' => ['value' => '0']]]) }}" 
                   class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-yellow-100 p-2">
                            <x-heroicon-o-clock class="h-5 w-5 text-yellow-600" />
                        </div>
                        <span class="font-medium text-gray-900">Verify Payments</span>
                    </div>
                    <x-heroicon-o-arrow-right class="h-5 w-5 text-gray-400" />
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">School Overview</h3>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @php
                    $classes = \App\Models\Standard::withCount('students')->get();
                @endphp
                @foreach($classes as $class)
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $class->name }}</div>
                        <div class="text-sm text-gray-500">{{ $class->students_count }} Students</div>
                        <div class="mt-2 text-xs text-gray-400">
                            Term Fees: TSH {{ number_format($class->term_one_fee + $class->term_two_fee, 0) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Widgets Grid --}}
    <div class="grid grid-cols-1 gap-6">
        @foreach ($this->getFooterWidgets() as $widget)
            @if ($widget::canView())
                @livewire($widget, key($widget))
            @endif
        @endforeach
    </div>

    {{-- Stats Modal --}}
    <x-filament::modal id="student-stats" width="2xl">
        <x-slot name="heading">
            Student Statistics Details
        </x-slot>
        
        <x-slot name="description">
            Detailed breakdown of student statistics
        </x-slot>
        
        <div class="space-y-4">
            @php
                $students = \App\Models\Student::count();
                $byGender = \App\Models\Student::selectRaw('gender, COUNT(*) as count')
                    ->groupBy('gender')
                    ->pluck('count', 'gender')
                    ->toArray();
                
                $byClass = \App\Models\Student::with('standard')
                    ->selectRaw('standard_id, COUNT(*) as count')
                    ->groupBy('standard_id')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->standard->name => $item->count])
                    ->toArray();
            @endphp
            
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="font-medium text-gray-900">Gender Distribution</h4>
                    <div class="mt-2 space-y-2">
                        @foreach($byGender as $gender => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">{{ ucfirst($gender) }}</span>
                                <span class="font-medium">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="font-medium text-gray-900">Class Distribution</h4>
                    <div class="mt-2 space-y-2 max-h-40 overflow-y-auto">
                        @foreach($byClass as $class => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">{{ $class }}</span>
                                <span class="font-medium">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <x-slot name="footerActions">
            <x-filament::button wire:click="$dispatch('close-modal', { id: 'student-stats' })">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
