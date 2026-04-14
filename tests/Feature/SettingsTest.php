<?php

namespace Tests\Feature;

use Tests\TestCase;

class SettingsTest extends TestCase
{
    public function test_settings_page_is_accessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Settings')
            ->has('locales')
            ->has('currentLocale')
            ->has('primaryColor')
            ->has('colorScheme')
        );
    }

    public function test_can_update_locale(): void
    {
        $response = $this->post('/settings/locale', ['locale' => 'en']);

        $response->assertRedirect();
        $response->assertCookie('locale', 'en', false);
    }

    public function test_update_locale_validates_value(): void
    {
        $response = $this->post('/settings/locale', ['locale' => 'xx']);

        $response->assertSessionHasErrors('locale');
    }

    public function test_update_locale_requires_value(): void
    {
        $response = $this->post('/settings/locale', []);

        $response->assertSessionHasErrors('locale');
    }

    public function test_can_update_color(): void
    {
        $response = $this->post('/settings/color', ['color' => '#FF5733']);

        $response->assertRedirect();
        $response->assertCookie('primary_color', '#FF5733', false);
    }

    public function test_update_color_validates_format(): void
    {
        $response = $this->post('/settings/color', ['color' => 'not-a-hex']);

        $response->assertSessionHasErrors('color');
    }

    public function test_update_color_requires_hash_prefix(): void
    {
        $response = $this->post('/settings/color', ['color' => 'FF5733']);

        $response->assertSessionHasErrors('color');
    }

    public function test_update_color_requires_value(): void
    {
        $response = $this->post('/settings/color', []);

        $response->assertSessionHasErrors('color');
    }

    public function test_can_update_theme(): void
    {
        $response = $this->post('/settings/theme', ['theme' => 'light']);

        $response->assertRedirect();
        $response->assertCookie('color_scheme', 'light', false);
    }

    public function test_update_theme_validates_value(): void
    {
        $response = $this->post('/settings/theme', ['theme' => 'invalid']);

        $response->assertSessionHasErrors('theme');
    }

    public function test_update_theme_requires_value(): void
    {
        $response = $this->post('/settings/theme', []);

        $response->assertSessionHasErrors('theme');
    }
}
