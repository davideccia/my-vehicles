<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Native\Mobile\Facades\Share;

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

    public function export(): HttpResponse|RedirectResponse
    {
        $data = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'data' => [
                'vehicle_service_types' => DB::table('vehicle_service_types')->get()->toArray(),
                'vehicles' => DB::table('vehicles')->get()->toArray(),
                'vehicle_refuels' => DB::table('vehicle_refuels')->get()->toArray(),
                'vehicle_services' => DB::table('vehicle_services')->get()->toArray(),
                'vehicle_service_reminders' => DB::table('vehicle_service_reminders')->get()->toArray(),
            ],
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'my-vehicles-'.now()->format('Y-m-d').'.json';

        if (function_exists('nativephp_call')) {
            $path = storage_path('app/private/'.$filename);
            file_put_contents($path, $json);
            Share::file('My Vehicles', 'Database backup', $path);

            return redirect()->back();
        }

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);

        $content = file_get_contents($request->file('file')->getPathname());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! isset($data['version'], $data['data'])) {
            return redirect()->back()->withErrors(['file' => 'settings.import_error']);
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () use ($data) {
            DB::table('vehicle_service_reminders')->delete();
            DB::table('vehicle_services')->delete();
            DB::table('vehicle_refuels')->delete();
            DB::table('vehicles')->delete();
            DB::table('vehicle_service_types')->delete();

            foreach ($data['data']['vehicle_service_types'] ?? [] as $row) {
                DB::table('vehicle_service_types')->insert((array) $row);
            }
            foreach ($data['data']['vehicles'] ?? [] as $row) {
                DB::table('vehicles')->insert((array) $row);
            }
            foreach ($data['data']['vehicle_refuels'] ?? [] as $row) {
                DB::table('vehicle_refuels')->insert((array) $row);
            }
            foreach ($data['data']['vehicle_services'] ?? [] as $row) {
                DB::table('vehicle_services')->insert((array) $row);
            }
            foreach ($data['data']['vehicle_service_reminders'] ?? [] as $row) {
                DB::table('vehicle_service_reminders')->insert((array) $row);
            }
        });

        DB::statement('PRAGMA foreign_keys = ON');

        return redirect()->back()->with('success', 'settings.import_success');
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
