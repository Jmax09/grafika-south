<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingDarkModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_exposes_theme_toggle_and_dark_token_layer(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('theme-dark', false);
        $response->assertSee('grafika-theme', false);
    }

    public function test_every_public_module_exposes_the_theme_toggle(): void
    {
        // Dark mode is available across all modules, not just the landing page.
        foreach ([route('landing'), route('products.index'), route('cart.index')] as $url) {
            $response = $this->get($url);

            $response->assertStatus(200);
            $response->assertSee('data-theme-toggle', false);
            $response->assertSee('grafika-theme', false);
        }
    }

    public function test_shared_token_layer_declares_all_three_layers(): void
    {
        $tokens = file_get_contents(resource_path('views/partials/design-tokens.blade.php'));

        // Layer 1 — primitives
        $this->assertStringContainsString('--p-cyan-500', $tokens);
        $this->assertStringContainsString('--p-ink-900', $tokens);

        // Layer 2 — semantic
        $this->assertStringContainsString('--bg-page', $tokens);
        $this->assertStringContainsString('--accent-color', $tokens);
        $this->assertStringContainsString('--text-main', $tokens);

        // Layer 3 — component
        $this->assertStringContainsString('--btn-primary-bg', $tokens);
        $this->assertStringContainsString('--card-border', $tokens);

        // Dark theme overrides
        $this->assertStringContainsString('html.theme-dark', $tokens);
    }

    public function test_both_layouts_include_the_shared_token_layer_and_controller(): void
    {
        foreach (['app', 'admin'] as $layout) {
            $html = file_get_contents(resource_path('views/layouts/' . $layout . '.blade.php'));

            $this->assertStringContainsString("@include('partials.design-tokens')", $html);
            $this->assertStringContainsString("@include('partials.theme-script')", $html);
        }
    }

    public function test_theme_toggle_button_is_accessible_and_wired_to_the_controller(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('aria-pressed="false"', false);
        $response->assertSee('Switch to dark theme', false);
        $response->assertSee('theme-toggle__icon--sun', false);
        $response->assertSee('theme-toggle__icon--moon', false);
    }

    public function test_dark_header_uses_the_brand_blue_token(): void
    {
        $tokens = file_get_contents(resource_path('views/partials/design-tokens.blade.php'));

        $this->assertStringContainsString('--header-bg', $tokens);
        $this->assertStringContainsString('--header-solid', $tokens);
        // The dark header is a deep brand blue, not near-black.
        $this->assertMatchesRegularExpression(
            '/html\.theme-dark\s*\{[^}]*--header-bg:\s*rgba\(12,\s*74,\s*110/s',
            $tokens
        );
    }
}
