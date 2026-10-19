<?php

namespace Tests\Feature;

use App\Listeners\MergeGuestCart;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CartQuantityConfigTest extends TestCase
{
    use RefreshDatabase;

    public static function limits(): array
    {
        return [[3, false], [3, true], [12, false], [12, true]];
    }

    #[DataProvider('limits')]
    public function test_configured_limit_is_used_throughout_cart(int $limit, bool $member): void
    {
        config(['cart.max_quantity' => $limit]);
        $this->withoutVite();
        $product = Product::create([
            'name' => '確認用商品', 'category' => 'other', 'volume' => '100g',
            'price' => 1000, 'is_active' => true,
        ]);
        if ($member) {
            $this->actingAs(User::factory()->create());
        }
        $payload = ['product_id' => $product->id, 'quantity' => $limit + 1];
        $this->post(route('carts.store'), $payload)->assertSessionHasErrors('quantity');
        $payload['quantity'] = $limit - 1;
        $this->post(route('carts.store'), $payload)->assertRedirect(route('carts.index'));
        $payload['quantity'] = 1;
        $this->post(route('carts.store'), $payload)->assertRedirect(route('carts.index'));
        $this->post(route('carts.store'), $payload)
            ->assertSessionHas('error', "同じ商品は{$limit}個までカートに入れられます。");
        $this->patch(route('carts.update', $product->id), ['quantity' => $limit + 1])
            ->assertSessionHasErrors('quantity');
        $this->patch(route('carts.update', $product->id), ['quantity' => $limit])
            ->assertRedirect(route('carts.index'));
        if ($member) {
            $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => $limit]);
        } else {
            $this->assertSame($limit, session('cart')[$product->id]);
        }
        foreach ([route('products.show', $product->id), route('carts.index')] as $url) {
            $response = $this->get($url)->assertOk();
            preg_match('/<select name="quantity"[^>]*>(.*?)<\/select>/s', $response->getContent(), $matches);
            preg_match_all('/<option value="(\d+)"/', $matches[1] ?? '', $options);
            $this->assertSame(range(1, $limit), array_map('intval', $options[1]));
        }
    }

    #[DataProvider('limits')]
    public function test_merge_caps_existing_and_new_items(int $limit, bool $existing): void
    {
        config(['cart.max_quantity' => $limit]);
        $user = User::factory()->create();
        $product = Product::create([
            'name' => '確認用商品', 'category' => 'other', 'volume' => '100g',
            'price' => 1000, 'is_active' => true,
        ]);
        if ($existing) {
            $cart = Cart::create(['user_id' => $user->id]);
            CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => $limit - 1]);
        }
        $this->withSession(['cart' => [$product->id => $existing ? 2 : $limit + 1]]);
        app(MergeGuestCart::class)->handle(new Login('web', $user, false));
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => $limit]);
        $this->assertFalse(session()->has('cart'));
    }
}
