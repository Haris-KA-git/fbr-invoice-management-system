<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\Item;
use App\Services\ItemImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemImportTest extends TestCase
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

    public function test_can_import_items_from_csv()
    {
        Storage::fake('local');

        $csvContent = "Name,HS_Code,Unit_of_Measure,Price,GST_Rate,Description\n";
        $csvContent .= "Test Item,1234.56.78,PCS,1000,17,Test Description\n";
        $csvContent .= "Another Item,9876.54.32,KG,500,12,Another Description";

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/items/import', [
            'file' => $file,
            'business_profile_id' => $this->businessProfile->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'hs_code' => '1234.56.78',
            'business_profile_id' => $this->businessProfile->id
        ]);
    }

    public function test_can_export_items_to_csv()
    {
        Item::factory()->create([
            'business_profile_id' => $this->businessProfile->id,
            'name' => 'Export Test Item',
            'hs_code' => '1111.22.33'
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/api/items/export?business_profile_id=' . $this->businessProfile->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Export Test Item', $response->getContent());
    }

    public function test_hs_code_is_mandatory()
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
}