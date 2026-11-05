<?php

namespace Tests\Feature;

use App\Models\HeroBanner;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageSectionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Custom Sublimation T-Shirt',
            'slug' => 'custom-sublimation-t-shirt',
            'category' => 'Apparel',
            'description' => 'Vibrant CMYK sublimation on polyester fabric.',
            'price' => 250.00,
            'base_price' => 250.00,
            'is_active' => true,
        ], $overrides));
    }

    public function test_landing_renders_the_redesigned_section_headings(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('Services Offered', false);
        $response->assertSee('how-heading', false);
        $response->assertSee('Why Grafika South', false);
        $response->assertSee('Print Products', false);
        $response->assertSee('Live Pricing', false);
        $response->assertSee('Order Tracking', false);
        $response->assertSee('Ready to put your design on press?', false);
    }

    public function test_landing_preserves_the_existing_search_contract(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('navbar-search-form', false);
        $response->assertSee('Search products', false);
        $response->assertSee('floatingSearchWidget', false);
        $response->assertSee('bubbleToggleBtn', false);
    }

    public function test_service_cards_link_to_their_category_routes(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('products.index', ['category' => 'Apparel']), false);
        $response->assertSee(route('products.index', ['category' => 'Identification']), false);
        $response->assertSee(route('products.index', ['category' => 'Marketing']), false);
        $response->assertSee(route('products.index', ['category' => 'Services']), false);
    }

    public function test_product_cards_link_to_the_product_detail_page(): void
    {
        $product = $this->makeProduct();

        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee(route('products.show', $product->slug), false);
        $response->assertSee('Custom Sublimation T-Shirt', false);
        $response->assertSee('₱250.00', false);
    }

    public function test_hero_carousel_renders_when_active_banners_exist(): void
    {
        HeroBanner::create([
            'image_path' => 'hero/test.png',
            'title' => 'Summer Promo',
            'link_url' => 'https://example.com',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('heroCarousel', false);
        $response->assertSee('heroCarouselTrack', false);
        $response->assertSee('Summer Promo', false);
        $response->assertSee('heroPrevBtn', false);
        $response->assertSee('heroDots', false);
    }

    public function test_landing_uses_no_hardcoded_hex_colours_in_inline_styles(): void
    {
        $response = $this->get(route('landing'));
        $html = $response->getContent();

        // Product colour swatches are literal on purpose - they represent real
        // print colours (grey / black / sand fabric), not UI chrome, so they
        // must never resolve to a theme token.
        $swatchAllowList = ['#94A3B8', '#1E293B', '#D6C7A8'];

        // Inline style attributes on the landing view must not carry raw hex values.
        preg_match_all('/style="([^"]*)"/', $html, $matches);

        foreach ($matches[1] as $inlineStyle) {
            $stripped = $inlineStyle;
            foreach ($swatchAllowList as $allowed) {
                $stripped = str_ireplace($allowed, '', $stripped);
            }

            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3,8}\b/',
                $stripped,
                'Landing page contains a hardcoded hex colour in an inline style: ' . $inlineStyle
            );
        }
    }
}
