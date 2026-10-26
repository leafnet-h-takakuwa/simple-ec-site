<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderShipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminOrderModalErrorsTest extends TestCase
{
    use RefreshDatabase;

    public static function invalidOrderForms(): array
    {
        return [
            'status' => [
                'admin.orders.updateStatus',
                ['_modal' => 'status', 'status' => 'invalid', 'contact_method' => 'none'],
                'status_modal',
            ],
            'fees' => [
                'admin.orders.fees.update',
                ['_modal' => 'fees', 'shipping_fee' => '-1'],
                'fees_modal',
            ],
            'payment' => [
                'admin.orders.payment.update',
                ['_modal' => 'payment', 'payment_method' => 'invalid'],
                'payment_modal',
            ],
            'b2 shipping' => [
                'admin.orders.b2Shippings.update',
                ['_modal' => 'b2_shippings', 'b2_shippings' => [['cool_type' => 'invalid']]],
                'b2_shippings_modal',
            ],
            'shipping' => [
                'admin.orders.shippings.update',
                ['_modal' => 'shippings', 'delivery_date' => 'invalid'],
                'shippings_modal',
            ],
            'cancel' => [
                'admin.orders.updateStatus',
                ['_modal' => 'cancel', 'status' => OrderStatus::CANCELLED->value, 'contact_method' => 'invalid'],
                'cancel_modal',
            ],
        ];
    }

    #[DataProvider('invalidOrderForms')]
    public function test_validation_errors_reopen_the_matching_modal(
        string $route,
        array $input,
        string $modalId
    ): void {
        $order = $this->createOrder();

        $this->patch(route($route, $order), $input)
            ->assertRedirect()
            ->assertSessionHasErrors()
            ->assertSessionHas('_old_input._modal', $input['_modal']);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('id="flash-toast"', false)
            ->assertSee('document.getElementById("'.$modalId.'")?.showModal();', false)
            ->assertSee('id="'.$modalId.'"', false)
            ->assertSee('入力内容を確認してください。');
    }

    public function test_business_status_errors_keep_the_status_modal_context(): void
    {
        $order = $this->createOrder();

        $this->patch(route('admin.orders.updateStatus', $order), [
            '_modal' => 'status',
            'status' => OrderStatus::SHIPPED->value,
            'contact_method' => 'none',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('error')
            ->assertSessionHas('_old_input._modal', 'status');

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('document.getElementById("status_modal")?.showModal();', false);
    }

    public function test_b2_shipping_rows_are_restored_with_existing_ids_and_new_rows(): void
    {
        $order = $this->createOrder();
        $shipping = OrderShipping::create([
            'order_id' => $order->id,
            'tracking_number' => null,
            'cool_type' => 'normal',
            'item_name' => '保存済み品名',
        ]);

        $this->patch(route('admin.orders.b2Shippings.update', $order), [
            '_modal' => 'b2_shippings',
            'b2_shippings' => [
                0 => ['id' => $shipping->id, 'cool_type' => 'invalid', 'item_name' => '入力中の品名'],
                3 => ['cool_type' => 'frozen', 'item_name' => '追加した品名'],
            ],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors()
            ->assertSessionHas('_old_input.b2_shippings.0.id', $shipping->id);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('name="b2_shippings[0][id]"', false)
            ->assertSee('value="'.$shipping->id.'"', false)
            ->assertSee('name="b2_shippings[0][item_name]"', false)
            ->assertSee('value="入力中の品名"', false)
            ->assertSee('name="b2_shippings[3][item_name]"', false)
            ->assertSee('value="追加した品名"', false);
    }

    public function test_shipping_rows_are_restored_with_existing_ids_and_new_rows(): void
    {
        $order = $this->createOrder();
        $shipping = OrderShipping::create([
            'order_id' => $order->id,
            'tracking_number' => null,
        ]);

        $this->patch(route('admin.orders.shippings.update', $order), [
            '_modal' => 'shippings',
            'delivery_date' => 'invalid',
            'delivery_time_slot' => 'morning',
            'shippings' => [
                0 => ['id' => $shipping->id, 'tracking_number' => '入力中の送り状番号'],
                2 => ['tracking_number' => '追加した送り状番号'],
            ],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('delivery_date')
            ->assertSessionHas('_old_input.shippings.0.id', $shipping->id);

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('name="shippings[0][id]"', false)
            ->assertSee('value="'.$shipping->id.'"', false)
            ->assertSee('value="入力中の送り状番号"', false)
            ->assertSee('name="shippings[2][tracking_number]"', false)
            ->assertSee('value="追加した送り状番号"', false)
            ->assertSee('value="invalid"', false);
    }

    public function test_shipped_orders_allow_shipping_corrections_but_reject_b2_edits(): void
    {
        $order = $this->createOrder();
        $order->forceFill(['status' => OrderStatus::SHIPPED])->save();
        $shipping = OrderShipping::create([
            'order_id' => $order->id,
            'tracking_number' => 'old-tracking',
            'cool_type' => 'normal',
            'item_name' => '保存済み品名',
        ]);

        $this->patch(route('admin.orders.shippings.update', $order), [
            '_modal' => 'shippings',
            'delivery_date' => '2026-11-01',
            'delivery_time_slot' => '',
            'shippings' => [
                ['id' => $shipping->id, 'tracking_number' => 'corrected-tracking'],
            ],
        ])->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('order_shippings', [
            'id' => $shipping->id,
            'tracking_number' => 'corrected-tracking',
        ]);

        $this->patch(route('admin.orders.b2Shippings.update', $order), [
            '_modal' => 'b2_shippings',
            'b2_shippings' => [
                ['id' => $shipping->id, 'cool_type' => 'frozen', 'item_name' => '更新しない品名'],
            ],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('b2_shippings')
            ->assertSessionHas('_old_input._modal', 'b2_shippings');

        $this->assertDatabaseHas('order_shippings', [
            'id' => $shipping->id,
            'cool_type' => 'normal',
            'item_name' => '保存済み品名',
        ]);
    }

    private function createOrder(): Order
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user);

        return Order::create([
            'status' => OrderStatus::RECEIVED,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
            'total_amount' => 1000,
            'orderer_name' => '注文者',
            'orderer_postal_code' => '123-4567',
            'orderer_address' => '東京都',
            'orderer_phone' => '090-1234-5678',
            'shipping_name' => '配送先',
            'shipping_postal_code' => '123-4567',
            'shipping_address' => '東京都',
            'shipping_phone' => '090-1234-5678',
            'order_source' => 'web',
        ]);
    }
}
