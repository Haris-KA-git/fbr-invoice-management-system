<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Services\CustomerImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerImportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $businessProfile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');
        $this->businessProfile = BusinessProfile::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_import_customers_from_csv()
    {
        Storage::fake('local');

        $csvContent = "Name,NTN_CNIC,Address,Contact_Phone,Contact_Email\n";
        $csvContent .= "Test Customer,1234567890123,Test Address,+92-300-1234567,test@example.com\n";
        $csvContent .= "Another Customer,9876543210987,Another Address,+92-321-9876543,another@example.com";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/customers/import', [
            'file' => $file,
            'business_profile_id' => $this->businessProfile->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'ntn_cnic' => '1234567890123',
            'business_profile_id' => $this->businessProfile->id
        ]);
    }

    public function test_can_export_customers_to_csv()
    {
        Customer::factory()->create([
            'business_profile_id' => $this->businessProfile->id,
            'name' => 'Export Test Customer'
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/api/customers/export?business_profile_id=' . $this->businessProfile->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Export Test Customer', $response->getContent());
    }
}