{{--
    Theme toggle button.

    The controller lives in layouts/app.blade.php <head>: it delegates on
    [data-theme-toggle], so this button needs no inline JavaScript.

    $variant: 'icon' (default, square button for the nav bar) | 'drawer' (full-width row)
--}}
@php
    $variant = $variant ?? 'icon';
@endphp

@if($variant === 'drawer')
    <button type="button"
            class="theme-toggle theme-toggle--drawer"
            data-theme-toggle
            aria-pressed="false"
            aria-label="Switch to dark theme"
            title="Switch to dark theme">
        <span class="theme-toggle__icons" aria-hidden="true">
            <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
            </svg>
            <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </span>
        <span class="theme-toggle__label">Appearance</span>
    </button>
@else
    <button type="button"
            class="theme-toggle"
            data-theme-toggle
            aria-pressed="false"
            aria-label="Switch to dark theme"
            title="Switch to dark theme">
        <span class="theme-toggle__icons" aria-hidden="true">
            <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
            </svg>
            <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </span>
    </button>
@endif
