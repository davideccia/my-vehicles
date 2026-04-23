<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />

        <native:bottom-nav label-visibility="labeled">
            <native:bottom-nav-item
                id="vehicles"
                icon="car.side.fill"
                :label="__('app.nav.vehicles')"
                url="/vehicles"
                :active="request()->is('vehicles*')"
            />
            <native:bottom-nav-item
                id="refuels"
                icon="fuelpump.fill"
                :label="__('app.nav.refuels')"
                url="/vehicle-refuels"
                :active="request()->is('vehicle-refuels*')"
            />
            <native:bottom-nav-item
                id="services"
                icon="wrench.fill"
                :label="__('app.nav.services')"
                url="/vehicle-services"
                :active="request()->is('vehicle-services*')"
            />
            <native:bottom-nav-item
                id="reports"
                icon="chart.bar.fill"
                :label="__('app.nav.reports')"
                url="/reports"
                :active="request()->is('reports*')"
            />
            <native:bottom-nav-item
                id="settings"
                icon="gearshape.fill"
                :label="__('app.nav.settings')"
                url="/settings"
                :active="request()->is('settings*')"
            />
        </native:bottom-nav>
    </body>
</html>
