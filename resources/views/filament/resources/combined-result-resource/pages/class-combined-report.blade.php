<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Reports & Analytics
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                    Class Combined Results
                </h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    View all students in a class with results across all subjects for a selected term and year.
                </div>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if ($standardId)
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $standardName ?? 'Selected Class' }}</span>
                    <span class="mx-2 text-gray-300 dark:text-gray-700">|</span>
                    <span>{{ str_replace('_', ' ', $term) }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ $year }}</span>
                @else
                    Select a class to begin.
                @endif
            </div>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Filters
            </x-slot>
            <x-slot name="description">
                Choose a class, term, and year.
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @if ($standardId)
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Students</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $studentsCount }}</div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Subjects</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $subjectsCount }}</div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Results Entered</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $resultsCount }}</div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Average Score</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $averageScore !== null ? $averageScore : '—' }}</div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Pass Rate</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $passRate !== null ? ($passRate . '%') : '—' }}</div>
                </div>
            </div>

            {{ $this->table }}
        @endif
    </div>
</x-filament-panels::page>
