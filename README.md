<div align="center">
  <img src="public/icon.png" alt="My Vehicles" width="120" />

  # My Vehicles

  A mobile-first vehicle management app for iOS and Android.

  ![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)
  ![Vue](https://img.shields.io/badge/Vue-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
  ![Inertia](https://img.shields.io/badge/Inertia-3-9553E9?style=flat&logo=inertia&logoColor=white)
  ![NativePHP](https://img.shields.io/badge/NativePHP-Mobile-blue?style=flat)
  ![Vuetify](https://img.shields.io/badge/Vuetify-4-1867C0?style=flat&logo=vuetify&logoColor=white)
</div>

---

## What It Does

**My Vehicles** lets you keep track of your personal vehicles and their maintenance history from your phone. For each vehicle you can:

- Store plate number, brand, model, year, and purchase date
- Log **fuel refuels** (date, liters, cost, mileage)
- Record **service interventions** (type, date, notes, mileage)
- Set **service reminders** based on mileage or time intervals
- Manage custom **service types** to categorise interventions

The app supports **Italian** and **English** locales and is optimised for portrait-mode mobile use.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | [Laravel 13](https://laravel.com) |
| Frontend | [Vue 3](https://vuejs.org) + [Inertia v3](https://inertiajs.com) |
| Mobile runtime | [NativePHP Mobile](https://nativephp.com) (iOS & Android) |
| UI components | [Vuetify 4](https://vuetifyjs.com) (Material Design 3) |
| Icons | [Material Design Icons](https://pictogrammers.com/library/mdi/) |
| CSS | [Tailwind CSS v4](https://tailwindcss.com) |
| Database | SQLite (per-device) |
| Testing | PHPUnit 12 |

---

## Getting Started

```bash
# Clone the repository
git clone https://github.com/your-username/my-vehicles.git
cd my-vehicles

# First-time setup (install deps, generate key, migrate, build)
composer run setup

# Start the development server
composer run dev
```

---

## Commands

```bash
# Development
composer run dev          # Start server, queue, logs, and Vite concurrently

# Testing
php artisan test --compact

# Code quality
composer run ci:check     # Lint + format + types + tests
composer run lint          # PHP (Pint)
npm run lint               # JS/TS/Vue (ESLint)
npm run format             # Prettier
npm run types:check        # vue-tsc

# After route/controller changes
php artisan wayfinder:generate
```

---

## Project Structure

```
app/
  Http/Controllers/    # VehicleController, VehicleRefuelController,
                       # VehicleServiceController, VehicleServiceReminderController ...
  Models/              # Vehicle, VehicleRefuel, VehicleService,
                       # VehicleServiceType, VehicleServiceReminder
  Enums/               # Locale (It, En)
resources/js/
  pages/               # Inertia pages (vehicles/, vehicle-refuels/,
                       # vehicle-services/, vehicle-service-reminders/, ...)
  layouts/             # MobileLayout.vue (fixed bottom nav)
  i18n/                # Italian (default) + English translations
```

---

## License

MIT