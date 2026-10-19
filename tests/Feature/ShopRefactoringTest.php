<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\InputLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShopRefactoringTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $product = Product::create($attributes + [
            'name' => '確認用商品', 'category' => 'other', 'volume' => '100g',
            'price' => 1200, 'is_active' => true,
        ]);
        if (isset($attributes['created_at'])) {
            $product->forceFill(['created_at' => $attributes['created_at']])->save();
        }

        return $product;
    }

    private function contact(): array
    {
        return [
            'orderer_name' => '確認 太郎', 'orderer_postal_code' => '123-4567',
            'orderer_address' => '東京都', 'orderer_phone' => '090-1234-5678',
            'orderer_email' => 'test@example.com', 'shipping_same' => '1', 'customer_note' => null,
        ];
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user);

        return $user;
    }

    public static function customers(): array
    {
        return [[false], [true]];
    }

    #[DataProvider('customers')]
    public function test_checkout_uses_the_same_cart_and_clears_it_after_success(bool $member): void
    {
        if ($member) {
            $this->actingAs(User::factory()->create());
        }
        $product = $this->product();
        $this->post(route('carts.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $this->post(route('orders.confirm'), $this->contact())
            ->assertRedirect(route('orders.confirm.show'));
        $this->get(route('orders.confirm.show'))->assertOk()->assertViewHas('total_amount', 2400);
        $this->assertSame([[
            'product_id' => $product->id, 'price' => 1200, 'quantity' => 2,
        ]], session('confirmed_items'));
        $this->post(route('orders.store'), ['fee_agreement' => '1'])
            ->assertRedirect(route('orders.complete'));
        $this->assertDatabaseHas('orders', ['total_amount' => 2400, 'payment_method' => PaymentMethod::CASH_ON_DELIVERY->value]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'subtotal' => 2400]);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertFalse(session()->has('cart'));
    }

    #[DataProvider('customers')]
    public function test_checkout_rejects_price_changes_and_unavailable_products(bool $member): void
    {
        if ($member) {
            $this->actingAs(User::factory()->create());
        }
        $product = $this->product();
        $this->post(route('carts.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $this->post(route('orders.confirm'), $this->contact());
        $this->get(route('orders.confirm.show'))->assertOk();
        $product->update(['price' => 1500]);
        $this->post(route('orders.store'), ['fee_agreement' => '1'])
            ->assertRedirect(route('carts.index'))->assertSessionHas('error');
        $this->assertFalse(session()->has('confirmed_items'));
        $this->get(route('orders.confirm.show'))->assertOk();
        $product->update(['is_active' => false]);
        $this->get(route('carts.index'))->assertViewHas('cart_items', fn ($items) => $items->isEmpty());
        $this->post(route('orders.store'), ['fee_agreement' => '1'])
            ->assertRedirect(route('carts.index'))->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_member_cart_retains_deleted_products_for_checkout_validation(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product();
        $this->post(route('carts.store'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('orders.confirm'), $this->contact());
        $this->get(route('orders.confirm.show'))->assertOk();
        $product->delete();
        $this->post(route('orders.store'), ['fee_agreement' => '1'])
            ->assertSessionHas('error', '購入できない商品が含まれています。カートの内容をご確認ください。');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_requires_a_separate_shipping_address_and_limits_contact_lengths(): void
    {
        $product = $this->product();
        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('orders.confirm'), array_replace($this->contact(), [
                'shipping_same' => '0', 'orderer_phone' => str_repeat('1', InputLimits::PHONE + 1),
            ]))->assertSessionHasErrors(['shipping_name', 'shipping_postal_code', 'shipping_address', 'shipping_phone', 'orderer_phone']);
    }

    public function test_product_save_shares_validation_defaults_and_image_removal(): void
    {
        $this->admin();
        Storage::fake('public');
        $payload = [
            'name' => '登録商品', 'category' => 'other', 'volume' => '200g',
            'price' => 2000, 'is_active' => '1', 'is_standard' => '1', 'description' => '説明',
        ];
        $this->post(route('admin.products.store'), $payload)->assertRedirect(route('admin.products.index'));
        $product = Product::firstOrFail();
        Storage::disk('public')->put('products/old.png', 'old');
        $product->update(['image_path' => 'products/old.png']);
        unset($payload['is_standard'], $payload['description']);
        $payload['remove_image'] = '1';
        $this->put(route('admin.products.update', $product->id), $payload)
            ->assertRedirect(route('admin.products.edit', $product->id));
        $product->refresh();
        $this->assertFalse($product->is_standard);
        $this->assertNull($product->description);
        $this->assertNull($product->image_path);
        Storage::disk('public')->assertMissing('products/old.png');
        $this->post(route('admin.products.store'), array_replace($payload, ['category' => 'invalid']))
            ->assertSessionHasErrors('category');
    }

    public function test_image_size_config_applies_to_create_and_update_and_guidance(): void
    {
        $this->admin();
        config(['shop.products.image_max_kb' => 1]);
        $product = $this->product();
        $payload = $product->only(['name', 'category', 'volume', 'price', 'is_active']);
        $payload['category'] = 'other';
        $payload['image'] = UploadedFile::fake()->createWithContent('image.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $payload['image']->sizeToReport = 2048;
        $this->post(route('admin.products.store'), $payload)->assertSessionHasErrors('image');
        $this->put(route('admin.products.update', $product->id), $payload)->assertSessionHasErrors('image');
        $this->get(route('admin.products.create'))->assertOk()->assertSee('最大1KB');
        $this->get(route('admin.products.edit', $product->id))->assertOk()->assertSee('最大1KB');
    }

    public function test_product_display_counts_ordering_and_new_badge_follow_config(): void
    {
        config(['shop.home.popular_products' => 1, 'shop.home.new_products' => 2, 'shop.pagination.products' => 2, 'shop.products.new_days' => 5]);
        $this->travelTo(now()->startOfSecond());
        $old = $this->product(['created_at' => now()->subDays(6)]);
        $standard = $this->product(['is_standard' => true, 'created_at' => now()->subDays(4)]);
        $new = $this->product();
        $this->product(['is_active' => false]);
        $this->assertFalse($old->isNew());
        $this->assertTrue($standard->isNew());
        $this->get(route('home'))->assertOk()
            ->assertViewHas('productsPopular', fn ($items) => $items->count() === 1 && $items->first()->is($new))
            ->assertViewHas('productsNew', fn ($items) => $items->count() === 2 && $items->first()->is($new));
        $this->get(route('products.index'))->assertOk()
            ->assertViewHas('products', fn ($items) => $items->perPage() === 2 && $items->total() === 3 && $items->first()->is($standard));
    }

    public function test_admin_counts_period_and_shared_scripts_render(): void
    {
        $this->admin();
        config(['shop.dashboard.recent_order_days' => 2, 'shop.pagination.admin_users' => 1]);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('直近2日間')->assertSee('過去2日間');
        $this->get(route('admin.users.index'))->assertOk()->assertViewHas('users', fn ($users) => $users->perPage() === 1);
        $order = Order::create([
            ...collect($this->contact())->except('shipping_same')->all(),
            'shipping_name' => '確認 太郎', 'shipping_postal_code' => '123-4567',
            'shipping_address' => '東京都', 'shipping_phone' => '090-1234-5678',
            'status' => OrderStatus::RECEIVED, 'payment_method' => PaymentMethod::BANK_TRANSFER,
            'total_amount' => 0,
        ]);
        $old = $order->replicate();
        $old->forceFill(['created_at' => now()->subDays(3), 'status' => OrderStatus::CANCELLED])->save();
        $shipped = $order->replicate();
        $shipped->forceFill(['status' => OrderStatus::SHIPPED])->save();
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('recent_orders', 2)->assertViewHas('active_orders', 1);
        $this->get(route('admin.orders.index', ['status' => 'active']))->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1 && $orders->first()->is($order));
        $this->get(route('admin.orders.create'))->assertOk()->assertSee('function refresh()', false)->assertSee("getElementById('copy-orderer')", false);
        $this->get(route('admin.orders.items.edit', $order->id))->assertOk()->assertSee('function refresh()', false);
        $this->get(route('admin.orders.customer.edit', $order->id))->assertOk()->assertSee("getElementById('copy-orderer')", false);
    }

    public function test_auth_forms_render_and_login_throttle_uses_config(): void
    {
        foreach (['login', 'register', 'password.request'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('maxlength="'.InputLimits::SHORT_TEXT.'"', false);
        }
        config(['auth.login_max_attempts' => 1, 'auth.login_decay_seconds' => 120]);
        $component = Volt::test('auth.login')->set('email', 'invalid@example.com')->set('password', 'wrong');
        $key = (new \ReflectionMethod($component->instance(), 'throttleKey'))->invoke($component->instance());
        RateLimiter::clear($key);
        $component->call('login')->assertHasErrors('email');
        $this->assertGreaterThan(60, RateLimiter::availableIn($key));
        $component->call('login')->assertHasErrors('email');
        $this->assertSame(1, RateLimiter::attempts($key));
        RateLimiter::clear($key);
    }
}
