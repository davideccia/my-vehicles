# Technology Stack

**Analysis Date:** 2026-04-16

## Languages

**Primary:**
- PHP 8.3+ - Backend, business logic, controllers, models
- TypeScript 5.2.2 - Frontend, Vue 3 components, utilities
- Vue 3.5.13 - UI framework, SFC (Single File Components)

**Secondary:**
- JavaScript (ES modules) - Build scripts, tooling
- SQL - Database queries via SQLite/MySQL through Eloquent ORM

## Runtime

**Environment:**
- PHP 8.3 (required in `composer.json`)
- Node.js 22 (specified in `.nvmrc`)

**Package Managers:**
- Composer 2+ - PHP dependency management
- npm 10+ (Node 22 bundled) - JavaScript dependency management
- Lockfiles: `composer.lock`, `package-lock.json` present

## Frameworks

**Core:**
- Laravel 13.0 - PHP web framework, routing, ORM, middleware
- Inertia.js 3.0 - Server-driven UI framework connecting Laravel to Vue
- Vue 3.5.13 - Progressive JavaScript framework for UI
- Vuetify 4.0.4 - Material Design 3 component library

**Build & Development:**
- Vite 8.0.0 - Frontend bundler and dev server
- Laravel Vite Plugin 3.0.0 - Vite integration for Laravel assets
- Vite Plugin Vuetify 2.1.3 - Vuetify auto-import and CSS tree-shaking
- Vite Plugin Wayfinder 0.1.3 - Typed route generation for Inertia

**Mobile Framework:**
- NativePHP Mobile 3.1 - Native iOS/Android app framework using Laravel + Vue
- NativePHP runtime: Persistent mode (fast ~5-30ms per request vs classic 200-300ms)

**Code Quality:**
- Laravel Pint 1.27 - PHP formatter (PSR-12 compliant)
- ESLint 9.17.0 - JavaScript linter
- ESLint Config Prettier 10.0.1 - ESLint compatibility with Prettier
- Prettier 3.4.2 - Code formatter for JS/TS/Vue
- vue-tsc 2.2.4 - TypeScript type-checking for Vue 3

**Testing & Development:**
- PHPUnit 12.5.12 - PHP testing framework
- Faker PHP 1.24 - Test data generation
- Mockery 1.6 - PHP mocking library

## Key Dependencies

**Critical:**
- `@inertiajs/inertia-laravel` ^3.0 - Server-side Inertia adapter
- `@inertiajs/vue3` ^3.0 - Vue 3 Inertia components
- `laravel/wayfinder` ^0.1.14 - Typed route generation, imported in controllers/frontend as `@/actions/*` or `@/routes/*`
- `nativephp/mobile` ^3.1 - Mobile app framework
- `vuetify` ^4.0.4 - Component library
- `vue-i18n` ^11.3.0 - Internationalization (Italian/English)

**UI & Icons:**
- `@mdi/font` ^7.4.47 - Material Design Icons (500+ icons, use `mdi-*` class names)
- `chart.js` ^4.5.1 - Charts (used in Reports.vue)
- `vue-chartjs` ^5.3.3 - Vue wrapper for Chart.js

**Utilities:**
- `@vueuse/core` ^12.8.2 - Vue 3 composition functions
- `tomloprod/colority` ^1.7 - Random color generator for chart datasets

**Development Utilities:**
- `concurrently` ^9.0.1 - Run multiple npm scripts simultaneously
- `laravel/boost` ^2.0 - Laravel performance utilities
- `laravel/pail` ^1.2.5 - Real-time log viewing
- `laravel/tinker` ^3.0 - Interactive REPL for Laravel
- `@types/node` ^22.13.5 - TypeScript types for Node.js
- `@vitejs/plugin-vue` ^6.0.0 - Official Vite Vue plugin
- `@stylistic/eslint-plugin` ^5.10.0 - ESLint stylistic rules
- `@vue/eslint-config-typescript` ^14.3.0 - Vue TypeScript ESLint config
- `eslint-plugin-vue` ^9.32.0 - Vue-specific ESLint rules
- `eslint-plugin-import` ^2.32.0 - Import/export linting
- `typescript-eslint` ^8.23.0 - TypeScript support for ESLint

## Configuration

**Environment:**
- `.env` file per Laravel conventions (required vars: `APP_KEY`, `DB_*`, `NATIVEPHP_*`)
- Config cache in `bootstrap/cache/` (cleared on artisan `config:clear`)
- NativePHP cleans sensitive env vars before bundling (see `config/nativephp.php` `cleanup_env_keys`)

**Build:**
- `vite.config.ts` - Configures Vite plugins (Laravel, Inertia, Vue, Vuetify, Wayfinder, NativePHP)
- `tsconfig.json` - TypeScript compiler config with path alias `@/*` → `resources/js/*`
- `.prettierrc` - Prettier config: 80-char line width, 4-space tabs, single quotes, tailwindcss plugin
- `eslint.config.js` - Flat config with Vue/TypeScript support, blank line rules around control flow
- `php-cs-fixer` config via Pint (PSR-12 formatting)
- `.editorconfig` - Consistent editor settings

**Laravel Configuration:**
- `config/app.php` - App name, timezone, locale defaults
- `config/auth.php` - Guard: `web` (session-based, no OAuth/API auth detected)
- `config/database.php` - Default: SQLite (`database/database.sqlite`), MySQL available
- `config/queue.php` - Default: `database` queue (synchronous by default)
- `config/services.php` - Postmark, Resend, AWS SES, Slack integrations available but not required
- `config/nativephp.php` - Mobile app configuration (version, orientation, Android/iOS SDKs, hot reload)
- `config/inertia.php` - Inertia.js settings

## Platform Requirements

**Development:**
- PHP 8.3+ with Composer
- Node.js 22 (from `.nvmrc`)
- SQLite or MySQL (SQLite default for development)
- Git

**Production (NativePHP Mobile):**
- iOS: macOS for building, Apple Developer Team ID for code signing (`NATIVEPHP_DEVELOPMENT_TEAM`)
- Android: Android SDK (compile_sdk 36, min_sdk 33, target_sdk 36), Gradle, JDK
- app_id required in env (reverse domain format, e.g., `com.company.app`)
- version and version_code in env for Play Store

---

*Stack analysis: 2026-04-16*
