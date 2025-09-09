<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $businessProfile;
    protected $customer;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->businessProfile = BusinessProfile::factory()->create(['user_id' => $this->user->id]);
        $this->customer = Customer::factory()->create(['business_profile_id' => $this->businessProfile->id]);
        $this->item = Item::factory()->create(['business_profile_id' => $this->businessProfile->id]);
    }

    public function test_user_can_create_invoice()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('invoices.store'), [
            'business_profile_id' => $this->businessProfile->id,
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'invoice_type' => 'sales',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'discount_rate' => 5,
                ]
            ]
        ]);

        $response->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('invoices', [
            'business_profile_id' => $this->businessProfile->id,
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_invoice_calculations_are_correct()
    {
        $this->actingAs($this->user);

        $this->post(route('invoices.store'), [
            'business_profile_id' => $this->businessProfile->id,
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'invoice_type' => 'sales',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'discount_rate' => 10,
                ]
            ]
        ]);

        $invoice = Invoice::latest()->first();
        
        // Quantity: 2, Unit Price: 1000, Discount: 10%
        // Line Total: 2000 - 200 (discount) = 1800
        // Tax: 1800 * 17% = 306
        // Total: 1800 + 306 = 2106
        
        $this->assertEquals(1800, $invoice->subtotal);
        $this->assertEquals(306, $invoice->sales_tax);
        $this->assertEquals(2106, $invoice->total_amount);
    }

    public function test_user_cannot_access_other_users_invoices()
    {
        $otherUser = User::factory()->create();
        $otherBusinessProfile = BusinessProfile::factory()->create(['user_id' => $otherUser->id]);
        $otherCustomer = Customer::factory()->create(['business_profile_id' => $otherBusinessProfile->id]);
        $otherItem = Item::factory()->create(['business_profile_id' => $otherBusinessProfile->id]);
        
        $otherInvoice = Invoice::factory()->create([
            'business_profile_id' => $otherBusinessProfile->id,
            'customer_id' => $otherCustomer->id,
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('invoices.show', $otherInvoice));
        $response->assertStatus(403);
    }
}