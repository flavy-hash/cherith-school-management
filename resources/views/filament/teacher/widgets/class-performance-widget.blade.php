<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Class Performance (Term One)
        </x-slot>

        @php($classes = $this->getClassPerformance())

        @if ($classes->isEmpty())
            <div class="text-sm text-gray-500 dark:text-gray-400">
                No assigned classes found.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($classes as $class)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $class['standard_name'] }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Students assessed: {{ $class['students_assessed'] }} | Results: {{ $class['results_count'] }}
                                </div>
                            </div>

                            <a
                                href="{{ url('/teacher/students?assignment_id=' . $class['assignment_id']) }}"
                                class="inline-flex items-center justify-center rounded-md bg-primary-600 px-3 py-2 text-xs font-semibold text-white hover:bg-primary-500"
                            >
                                View Students
                            </a>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Average</div>
                                <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $class['avg_score'] !== null ? $class['avg_score'] : '—' }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Pass rate</div>
                                <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $class['pass_rate'] !== null ? $class['pass_rate'] . '%' : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
