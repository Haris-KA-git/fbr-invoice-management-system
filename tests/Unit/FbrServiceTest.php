<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\User;
use App\Services\FbrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FbrServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $fbrService;
    protected $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fbrService = new FbrService();
        
        $user = User::factory()->create();
        $businessProfile = BusinessProfile::factory()->create(['user_id' => $user->id]);
        $customer = Customer::factory()->create(['business_profile_id' => $businessProfile->id]);
        
        $this->invoice = Invoice::factory()->create([
            'business_profile_id' => $businessProfile->id,
            'customer_id' => $customer->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_can_queue_invoice_for_fbr_submission()
    {
        $result = $this->fbrService->queueInvoice($this->invoice);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('fbr_queue', [
            'invoice_id' => $this->invoice->id,
            'action' => 'validate',
            'status' => 'pending',
        ]);
    }

    public function test_can_prepare_invoice_data_for_fbr()
    {
        $reflection = new \ReflectionClass($this->fbrService);
        $method = $reflection->getMethod('prepareInvoiceData');
        $method->setAccessible(true);
        
        $data = $method->invoke($this->fbrService, $this->invoice);
        
        $this->assertArrayHasKey('InvoiceNumber', $data);
        $this->assertArrayHasKey('InvoiceDate', $data);
        $this->assertArrayHasKey('Seller', $data);
        $this->assertArrayHasKey('Buyer', $data);
        $this->assertArrayHasKey('Items', $data);
        $this->assertArrayHasKey('Summary', $data);
    }
}