<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Invoice;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $businessProfile;
    protected $customer;
    protected $item;
    protected $invoice;
    protected $qrCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->user = User::factory()->create();
        $this->businessProfile = BusinessProfile::factory()->create(['user_id' => $this->user->id]);
        $this->customer = Customer::factory()->create(['business_profile_id' => $this->businessProfile->id]);
        $this->item = Item::factory()->create(['business_profile_id' => $this->businessProfile->id]);
        $this->invoice = Invoice::factory()->create([
            'business_profile_id' => $this->businessProfile->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);
        
        $this->qrCodeService = new QrCodeService();
    }

    public function test_can_generate_qr_code_for_invoice()
    {
        $qrPath = $this->qrCodeService->generateInvoiceQrCode($this->invoice);
        
        $this->assertNotNull($qrPath);
        Storage::disk('public')->assertExists($qrPath);
        
        $this->invoice->refresh();
        $this->assertEquals($qrPath, $this->invoice->qr_code_path);
        $this->assertNotNull($this->invoice->fbr_verification_url);
    }

    public function test_qr_code_contains_required_fbr_fields()
    {
        $qrData = $this->qrCodeService->getQrCodeData($this->invoice);
        
        $this->assertArrayHasKey('InvoiceNumber', $qrData);
        $this->assertArrayHasKey('STRN', $qrData);
        $this->assertArrayHasKey('CustomerNTN', $qrData);
        $this->assertArrayHasKey('InvoiceDate', $qrData);
        $this->assertArrayHasKey('TotalAmount', $qrData);
        $this->assertArrayHasKey('SalesTax', $qrData);
        $this->assertArrayHasKey('VerificationURL', $qrData);
        
        $this->assertEquals($this->invoice->invoice_number, $qrData['InvoiceNumber']);
        $this->assertEquals($this->invoice->invoice_date->format('Y-m-d'), $qrData['InvoiceDate']);
    }

    public function test_qr_code_displays_in_invoice_table()
    {
        $this->qrCodeService->generateInvoiceQrCode($this->invoice);
        
        $this->actingAs($this->user);
        
        $response = $this->get(route('invoices.index'));
        $response->assertStatus(200);
        $response->assertSee('QR Code');
    }

    public function test_qr_code_only_generated_after_fbr_submission()
    {
        // Test that QR code is not generated for non-submitted invoices
        $this->invoice->update(['fbr_status' => 'pending']);
        
        $qrPath = $this->qrCodeService->generateInvoiceQrCode($this->invoice);
        
        $this->assertEquals('', $qrPath);
        $this->invoice->refresh();
        $this->assertNull($this->invoice->qr_code_path);
    }

    public function test_qr_code_contains_fbr_submission_status()
    {
        $this->invoice->update([
            'fbr_status' => 'submitted',
            'usin' => '1234567890123456'
        ]);
        
        $this->qrCodeService->generateInvoiceQrCode($this->invoice);
        
        // The QR code content should indicate FBR submission status
        $this->invoice->refresh();
        $this->assertNotNull($this->invoice->qr_code_path);
        $this->assertNotNull($this->invoice->fbr_verification_url);
    }
}