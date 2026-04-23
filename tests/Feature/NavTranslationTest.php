<?php

namespace Tests\Feature;

use Tests\TestCase;

class NavTranslationTest extends TestCase
{
    public function test_nav_keys_exist_in_english(): void
    {
        app()->setLocale('en');

        $this->assertSame('Vehicles', __('app.nav.vehicles'));
        $this->assertSame('Refuels', __('app.nav.refuels'));
        $this->assertSame('Services', __('app.nav.services'));
        $this->assertSame('Reports', __('app.nav.reports'));
        $this->assertSame('Settings', __('app.nav.settings'));
    }

    public function test_nav_keys_exist_in_italian(): void
    {
        app()->setLocale('it');

        $this->assertSame('Veicoli', __('app.nav.vehicles'));
        $this->assertSame('Rifornimenti', __('app.nav.refuels'));
        $this->assertSame('Manutenzioni', __('app.nav.services'));
        $this->assertSame('Report', __('app.nav.reports'));
        $this->assertSame('Impostaz.', __('app.nav.settings'));
    }
}
