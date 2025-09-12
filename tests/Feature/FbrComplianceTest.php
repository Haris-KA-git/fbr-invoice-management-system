<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\FbrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FbrComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $businessProfile;
    protected $customer;
    protected $item;
    protected $invoice;
    protected $fbrService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->businessProfile = BusinessProfile::factory()->create([
            'user_id' => $this->user->id,
            'strn_ntn' => '1234567890123',
        ]);
        $this->customer = Customer::factory()->create([
            'business_profile_id' => $this->businessProfile->id,
            'ntn_cnic' => '9876543210987',
            'customer_type' => 'registered',
        ]);
        $this->item = Item::factory()->create([
            'business_profile_id' => $this->businessProfile->id,
            'hs_code' => '8471.30.00',
        ]);
        $this->invoice = Invoice::factory()->create([
            'business_profile_id' => $this->businessProfile->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);
        
        InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'item_id' => $this->item->id,
        ]);
        
        $this->fbrService = app(FbrService::class);
    }

    public function test_invoice_json_includes_all_mandatory_fbr_fields()
    {
        $reflection = new \ReflectionClass($this->fbrService);
        $method = $reflection->getMethod('prepareInvoiceData');
        $method->setAccessible(true);
        
        $data = $method->invoke($this->fbrService, $this->invoice);
        
        // Check main invoice fields
        $this->assertArrayHasKey('InvoiceNumber', $data);
        $this->assertArrayHasKey('InvoiceType', $data);
        $this->assertArrayHasKey('DocumentType', $data);
        $this->assertArrayHasKey('InvoiceDate', $data);
        $this->assertArrayHasKey('InvoiceTime', $data);
        $this->assertArrayHasKey('CurrencyCode', $data);
        $this->assertArrayHasKey('TotalAmount', $data);
        $this->assertArrayHasKey('TotalSalesTax', $data);
        
        // Check seller fields
        $this->assertArrayHasKey('Seller', $data);
        $this->assertArrayHasKey('NTN', $data['Seller']);
        $this->assertArrayHasKey('STRN', $data['Seller']);
        $this->assertArrayHasKey('BusinessName', $data['Seller']);
        $this->assertArrayHasKey('Address', $data['Seller']);
        $this->assertArrayHasKey('ProvinceCode', $data['Seller']);
        
        // Check buyer fields
        $this->assertArrayHasKey('Buyer', $data);
        $this->assertArrayHasKey('Name', $data['Buyer']);
        $this->assertArrayHasKey('NTN', $data['Buyer']);
        $this->assertArrayHasKey('Address', $data['Buyer']);
        
        // Check items structure
        $this->assertArrayHasKey('Items', $data);
        $this->assertIsArray($data['Items']);
        
        if (!empty($data['Items'])) {
            $item = $data['Items'][0];
            $this->assertArrayHasKey('HSCode', $item);
            $this->assertArrayHasKey('UOMCode', $item);
            $this->assertArrayHasKey('SalesTaxRate', $item);
            $this->assertArrayHasKey('SalesTaxAmount', $item);
            $this->assertArrayHasKey('TaxableAmount', $item);
        }
    }

    public function test_hs_code_is_mandatory_for_items()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('items.store'), [
            'business_profile_id' => $this->businessProfile->id,
            'item_code' => 'TEST001',
            'name' => 'Test Item',
            'unit_of_measure' => 'PCS',
            'tax_rate' => 17,
            'price' => 1000,
            // Missing hs_code
        ]);

        $response->assertSessionHasErrors('hs_code');
    }

    public function test_invoice_includes_hs_code_in_pdf()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('invoices.download-pdf', $this->invoice));
        $response->assertStatus(200);
        
        // Check if PDF contains HS Code
        $content = $response->getContent();
        $this->assertStringContainsString('HS Code', $content);
        $this->assertStringContainsString($this->item->hs_code, $content);
    }
}