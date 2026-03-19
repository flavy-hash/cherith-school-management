<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enter Results</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold">Enter Student Results</h1>
                <p class="text-gray-600">Subject: <span class="font-medium">{{ $teacherSubject->subject->name }}</span></p>
            </div>

            <div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded bg-gray-900 text-white">Logout</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-800 border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 text-red-800 border border-red-200">
                <div class="font-medium mb-1">Please fix the errors below.</div>
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('teacher.results.index') }}" class="mb-6 p-4 rounded bg-white border">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select name="standard_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">Select class...</option>
                        @foreach ($standards as $standard)
                            <option value="{{ $standard->id }}" @selected($selectedStandardId === $standard->id)>
                                {{ $standard->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select name="term" class="w-full border rounded px-3 py-2" required>
                        <option value="term_one" @selected($term === 'term_one')>Term One</option>
                        <option value="term_two" @selected($term === 'term_two')>Term Two</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <input type="number" name="year" value="{{ $year }}" class="w-full border rounded px-3 py-2" min="2000" max="2100" required>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Load Students</button>
            </div>
        </form>

        @if ($selectedStandardId)
            <form method="POST" action="{{ route('teacher.results.store') }}" class="p-4 rounded bg-white border">
                @csrf
                <input type="hidden" name="standard_id" value="{{ $selectedStandardId }}">
                <input type="hidden" name="term" value="{{ $term }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left text-sm font-semibold p-2 border">Admission #</th>
                                <th class="text-left text-sm font-semibold p-2 border">Student</th>
                                <th class="text-left text-sm font-semibold p-2 border">Score (0-100)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr>
                                    <td class="p-2 border text-sm">{{ $student->admission_number }}</td>
                                    <td class="p-2 border text-sm">{{ $student->full_name }}</td>
                                    <td class="p-2 border">
                                        <input
                                            type="number"
                                            name="scores[{{ $student->id }}]"
                                            value="{{ old('scores.' . $student->id, $existing[$student->id] ?? '') }}"
                                            min="0"
                                            max="100"
                                            class="w-32 border rounded px-2 py-1"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-2 border text-sm" colspan="3">No students found for this class.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white">Save Results</button>
                </div>
            </form>
        @endif
    </div>
</body>
</html>
