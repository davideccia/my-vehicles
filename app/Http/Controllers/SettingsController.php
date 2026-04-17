<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Settings', [
            'locales' => array_map(
                static fn (Locale $locale) => ['value' => $locale->value, 'label' => $locale->label()],
                Locale::cases(),
            ),
            'currentLocale' => App::getLocale(),
        ]);
    }

    public function updateLocale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', new Enum(Locale::class)],
        ]);

        return redirect()->back()->withCookie(
            cookie()->forever('locale', $validated['locale'])
        );
    }

    public function updateColor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        return redirect()->back()->withCookie(
            cookie()->forever('primary_color', $validated['color'])
        );
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:dark,light'],
        ]);

        return redirect()->back()->withCookie(
            cookie()->forever('color_scheme', $validated['theme'])
        );
    }

    public function resetDb(): RedirectResponse
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::table('vehicle_service_reminders')->delete();
            DB::table('vehicle_services')->delete();
            DB::table('vehicle_refuels')->delete();
            DB::table('vehicles')->delete();
            DB::table('vehicle_service_types')->delete();
            DB::table('users')->delete();
        });

        DB::statement('PRAGMA foreign_keys = ON');

        Artisan::call('db:seed');

        return redirect()->back()->with('success', 'settings.reset_success');
    }
}
