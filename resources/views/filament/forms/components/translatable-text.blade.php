@php
    use Illuminate\Support\Str;
@endphp

<div x-data="{ activeTab: '{{ config('app.fallback_locale', 'en') }}' }" class="space-y-4">
    <div class="flex space-x-1 border-b">
        @foreach($getLocales() as $locale)
            <button
                type="button"
                x-on:click="activeTab = '{{ $locale }}'"
                x-bind:class="activeTab === '{{ $locale }}' ? 'border-b-2 border-primary-500 text-primary-600' : 'text-gray-500 hover:text-gray-700'"
                class="px-3 py-2 text-sm font-medium transition-colors"
            >
                {{ strtoupper($locale) }}
            </button>
        @endforeach
    </div>

    @foreach($getFields() as $field)
        <div x-show="activeTab === '{{ $field->getName() }}'" x-cloak>
            {{ $field }}
        </div>
    @endforeach
</div>
