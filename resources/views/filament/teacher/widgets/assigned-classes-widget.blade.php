<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Assigned Classes
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->getAssignments() as $assignment)
                <a
                    href="{{ url('/teacher/students?assignment_id=' . $assignment->id) }}"
                    class="block rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900"
                >
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $assignment->standard?->name ?? 'Any Class' }}
                    </div>
                    <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                        {{ $assignment->subject->name }}
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
