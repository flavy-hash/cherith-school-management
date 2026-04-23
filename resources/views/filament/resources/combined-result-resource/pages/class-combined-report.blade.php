<x-filament-panels::page>
    <style>
        /* ── Widget Dashboard Styles ── */
        .cr-widget-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
        }
        @media (max-width: 1280px) { .cr-widget-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px)  { .cr-widget-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px)  { .cr-widget-grid { grid-template-columns: 1fr; } }

        .cr-widget {
            position: relative;
            border-radius: 1rem;
            padding: 1.25rem;
            overflow: hidden;
            transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.25s cubic-bezier(.4,0,.2,1);
            cursor: default;
        }
        .cr-widget:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        }

        /* ── Colour themes ── */
        .cr-widget--students  { background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); }
        .cr-widget--subjects  { background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%); }
        .cr-widget--results   { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
        .cr-widget--average   { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        .cr-widget--pass      { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }

        /* Decorative circle */
        .cr-widget::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            pointer-events: none;
        }
        .cr-widget::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -10px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
        }

        .cr-widget__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .cr-widget__label {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .cr-widget__icon {
            width: 38px;
            height: 38px;
            border-radius: 0.625rem;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            flex-shrink: 0;
        }
        .cr-widget__icon svg {
            width: 20px;
            height: 20px;
            color: #fff;
        }
        .cr-widget__value {
            font-size: 1.85rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            position: relative;
            z-index: 1;
        }
        .cr-widget__sub {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            position: relative;
            z-index: 1;
        }

        /* Progress bar for pass rate */
        .cr-progress-track {
            margin-top: 0.75rem;
            height: 6px;
            border-radius: 3px;
            background: rgba(255,255,255,0.2);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .cr-progress-fill {
            height: 100%;
            border-radius: 3px;
            background: #fff;
            transition: width 0.8s cubic-bezier(.4,0,.2,1);
        }

        /* Fade-in animation */
        @keyframes crFadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .cr-widget { animation: crFadeUp 0.45s ease-out both; }
        .cr-widget:nth-child(2) { animation-delay: 0.06s; }
        .cr-widget:nth-child(3) { animation-delay: 0.12s; }
        .cr-widget:nth-child(4) { animation-delay: 0.18s; }
        .cr-widget:nth-child(5) { animation-delay: 0.24s; }

        /* ── Page Header Banner ── */
        .cr-header-banner {
            position: relative;
            border-radius: 1.25rem;
            padding: 1.75rem 2rem;
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #1e293b 100%);
            overflow: hidden;
            animation: crFadeUp 0.4s ease-out both;
        }
        .dark .cr-header-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            border: 1px solid rgba(99, 102, 241, 0.15);
        }
        .cr-header-banner::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -30px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .cr-header-banner::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        .cr-header__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        .cr-header__left {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        .cr-header__icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }
        .cr-header__icon-wrap svg {
            width: 24px;
            height: 24px;
            color: #fff;
        }
        .cr-header__breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #818cf8;
            margin-bottom: 0.25rem;
        }
        .cr-header__breadcrumb svg {
            width: 14px;
            height: 14px;
        }
        .cr-header__title {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            background: linear-gradient(135deg, #e2e8f0, #f8fafc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.35rem;
        }
        .cr-header__desc {
            font-size: 0.85rem;
            color: #94a3b8;
            line-height: 1.5;
            max-width: 480px;
        }
        .cr-header__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            backdrop-filter: blur(4px);
            font-size: 0.8rem;
            color: #c7d2fe;
            font-weight: 600;
            white-space: nowrap;
            animation: crFadeUp 0.5s ease-out 0.15s both;
        }
        .cr-header__badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
            animation: crPulse 2s ease-in-out infinite;
        }
        .cr-header__badge-sep {
            color: rgba(148, 163, 184, 0.4);
        }
        .cr-header__empty {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border-radius: 9999px;
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.25);
            font-size: 0.8rem;
            color: #fbbf24;
            font-weight: 500;
            animation: crFadeUp 0.5s ease-out 0.15s both;
        }
        .cr-header__empty svg {
            width: 16px;
            height: 16px;
        }
        @keyframes crPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>

    <div class="space-y-6">
        {{-- ── Page Header Banner ── --}}
        <div class="cr-header-banner">
            <div class="cr-header__top">
                <div class="cr-header__left">
                    {{-- Icon --}}
                    <div class="cr-header__icon-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    {{-- Text --}}
                    <div>
                        <div class="cr-header__breadcrumb">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            Reports & Analytics
                        </div>
                        <h2 class="cr-header__title">Class Combined Results</h2>
                        <p class="cr-header__desc">
                            View all students in a class with results across all subjects for a selected term and year.
                        </p>
                    </div>
                </div>

                {{-- Badge / Empty state --}}
                @if ($standardId)
                    <div class="cr-header__badge">
                        <span class="cr-header__badge-dot"></span>
                        {{ $standardName ?? 'Selected Class' }}
                        <span class="cr-header__badge-sep">|</span>
                        {{ ucwords(str_replace('_', ' ', $term)) }}
                        <span class="cr-header__badge-sep">•</span>
                        {{ $year }}
                    </div>
                @else
                    <div class="cr-header__empty">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        Select a class to begin
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Filters ── --}}
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
            {{-- ── Dashboard Widgets ── --}}
            <div class="cr-widget-grid">
                {{-- Students --}}
                <div class="cr-widget cr-widget--students">
                    <div class="cr-widget__header">
                        <span class="cr-widget__label">Students</span>
                        <span class="cr-widget__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </span>
                    </div>
                    <div class="cr-widget__value">{{ $studentsCount }}</div>
                    <div class="cr-widget__sub">Enrolled in this class</div>
                </div>

                {{-- Subjects --}}
                <div class="cr-widget cr-widget--subjects">
                    <div class="cr-widget__header">
                        <span class="cr-widget__label">Subjects</span>
                        <span class="cr-widget__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </span>
                    </div>
                    <div class="cr-widget__value">{{ $subjectsCount }}</div>
                    <div class="cr-widget__sub">Total subjects offered</div>
                </div>

                {{-- Results Entered --}}
                <div class="cr-widget cr-widget--results">
                    <div class="cr-widget__header">
                        <span class="cr-widget__label">Results Entered</span>
                        <span class="cr-widget__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                            </svg>
                        </span>
                    </div>
                    <div class="cr-widget__value">{{ $resultsCount }}</div>
                    @php
                        $expectedResults = $studentsCount * $subjectsCount;
                    @endphp
                    <div class="cr-widget__sub">
                        of {{ $expectedResults }} expected
                    </div>
                    @if ($expectedResults > 0)
                        <div class="cr-progress-track">
                            <div class="cr-progress-fill" style="width: {{ min(round(($resultsCount / $expectedResults) * 100), 100) }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- Average Score --}}
                <div class="cr-widget cr-widget--average">
                    <div class="cr-widget__header">
                        <span class="cr-widget__label">Average Score</span>
                        <span class="cr-widget__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </span>
                    </div>
                    <div class="cr-widget__value">{{ $averageScore !== null ? $averageScore : '—' }}</div>
                    <div class="cr-widget__sub">
                        @if ($averageScore !== null)
                            @if ($averageScore >= 75)
                                Excellent performance
                            @elseif ($averageScore >= 50)
                                Satisfactory performance
                            @else
                                Needs improvement
                            @endif
                        @else
                            No data available
                        @endif
                    </div>
                    @if ($averageScore !== null)
                        <div class="cr-progress-track">
                            <div class="cr-progress-fill" style="width: {{ min($averageScore, 100) }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- Pass Rate --}}
                <div class="cr-widget cr-widget--pass">
                    <div class="cr-widget__header">
                        <span class="cr-widget__label">Pass Rate</span>
                        <span class="cr-widget__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </span>
                    </div>
                    <div class="cr-widget__value">{{ $passRate !== null ? ($passRate . '%') : '—' }}</div>
                    <div class="cr-widget__sub">
                        @if ($passRate !== null)
                            Students scoring 50% and above
                        @else
                            No data available
                        @endif
                    </div>
                    @if ($passRate !== null)
                        <div class="cr-progress-track">
                            <div class="cr-progress-fill" style="width: {{ min($passRate, 100) }}%"></div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Results Table ── --}}
            {{ $this->table }}
        @endif
    </div>
</x-filament-panels::page>
