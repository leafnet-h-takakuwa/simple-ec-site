<?php

namespace Tests\Feature;

use App\Enums\OrderSource;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user);

        return $user;
    }

    private function customerData(array $overrides = []): array
    {
        return $overrides + [
            'name' => '台帳 花子',
            'postal_code' => '123-4567',
            'address' => '東京都千代田区',
            'phone' => '090-1234-5678',
            'email' => 'customer@example.com',
            'memo' => '対応メモ',
            'is_orderer' => '1',
            'is_recipient' => '0',
        ];
    }

    private function product(): Product
    {
        return Product::create([
            'name' => '内容量確認商品',
            'category' => 'other',
            'volume' => '250g',
            'price' => 1200,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_update_search_and_soft_delete_customers(): void
    {
        $this->admin();

        $this->get(route('admin.customers.index'))->assertOk();
        $this->post(route('admin.customers.store'), $this->customerData([
            'is_orderer' => '0',
            'is_recipient' => '0',
        ]))->assertSessionHasErrors('is_orderer');
        $this->assertDatabaseCount('customers', 0);

        $this->post(route('admin.customers.store'), $this->customerData())
            ->assertRedirect(route('admin.customers.index'));
        $customer = Customer::firstOrFail();

        $this->get(route('admin.customers.index', ['keyword' => '対応メモ']))
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 1);

        $this->put(route('admin.customers.update', $customer), $this->customerData([
            'name' => '台帳 花子 更新',
            'memo' => '更新後メモ',
        ]))->assertRedirect(route('admin.customers.show', $customer));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => '台帳 花子 更新',
            'memo' => '更新後メモ',
        ]);

        $this->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));
        $this->assertSoftDeleted($customer);
    }

    public function test_customer_creation_rejects_duplicates_after_normalizing_name_and_numbers(): void
    {
        $this->admin();
        Customer::create($this->customerData([
            'name' => '台帳　花子',
            'phone' => '０９０－１２３４－５６７８',
            'postal_code' => '１２３－４５６７',
        ]));

        $this->post(route('admin.customers.store'), $this->customerData())
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_web_order_reuses_matching_customer_and_keeps_existing_details(): void
    {
        $product = $this->product();
        $existing = Customer::create([
            'name' => '受取 花子',
            'postal_code' => '９８７－６５４３',
            'address' => '台帳に登録済みの住所',
            'phone' => '０８０－９８７６－５４３２',
            'email' => 'saved@example.com',
            'memo' => '上書きしないメモ',
            'is_orderer' => true,
            'is_recipient' => false,
        ]);
        $this->post(route('carts.store'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('orders.confirm'), [
            'orderer_name' => '注文 太郎',
            'orderer_postal_code' => '111-1111',
            'orderer_address' => '注文者住所',
            'orderer_phone' => '090-1111-1111',
            'orderer_email' => 'orderer@example.com',
            'shipping_same' => '0',
            'shipping_name' => '受取花子',
            'shipping_postal_code' => '9876543',
            'shipping_address' => '今回の注文住所',
            'shipping_phone' => '08098765432',
        ])->assertRedirect(route('orders.confirm.show'));
        $this->get(route('orders.confirm.show'))->assertOk();
        $this->post(route('orders.store'), ['fee_agreement' => '1'])
            ->assertRedirect(route('orders.complete'));

        $this->assertDatabaseCount('customers', 2);
        $this->assertDatabaseHas('customers', [
            'id' => $existing->id,
            'address' => '台帳に登録済みの住所',
            'email' => 'saved@example.com',
            'memo' => '上書きしないメモ',
            'is_orderer' => true,
            'is_recipient' => true,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'volume' => '250g',
        ]);
        $this->get(route('orders.complete'))->assertOk()->assertSee('内容量：250g');
    }

    public function test_admin_created_order_is_registered_in_customer_ledger(): void
    {
        $this->admin();
        $product = $this->product();

        $this->post(route('admin.orders.store'), [
            'order_source' => OrderSource::PHONE->value,
            'payment_method' => 'cod',
            'orderer_name' => '電話注文 太郎',
            'orderer_postal_code' => '111-2222',
            'orderer_address' => '東京都',
            'orderer_phone' => '090-2222-3333',
            'shipping_name' => '電話注文 太郎',
            'shipping_postal_code' => '111-2222',
            'shipping_address' => '東京都',
            'shipping_phone' => '090-2222-3333',
            'items' => [[
                'product_id' => $product->id,
                'product_name' => $product->name,
                'volume' => $product->volume,
                'price' => $product->price,
                'quantity' => 1,
                'tax_rate' => 8,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', [
            'name' => '電話注文 太郎',
            'is_orderer' => true,
            'is_recipient' => false,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'volume' => '250g',
        ]);
    }
}
