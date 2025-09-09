<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessProfiles = BusinessProfile::all();

        foreach ($businessProfiles as $profile) {
            $customers = $profile->customers;
            $items = $profile->items;
            
            if ($customers->isEmpty() || $items->isEmpty()) {
                continue;
            }

            // Create sample invoices
            for ($i = 1; $i <= 5; $i++) {
                $customer = $customers->random();
                $invoiceDate = now()->subDays(rand(1, 30));
                
                $invoice = Invoice::create([
                    'business_profile_id' => $profile->id,
                    'customer_id' => $customer->id,
                    'user_id' => $profile->user_id,
                    'invoice_number' => 'INV-' . $invoiceDate->format('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'invoice_type' => 'sales',
                    'subtotal' => 0,
                    'sales_tax' => 0,
                    'total_amount' => 0,
                    'fbr_status' => collect(['pending', 'validated', 'submitted'])->random(),
                ]);

                // Add random items to invoice
                $selectedItems = $items->random(rand(1, 3));
                $subtotal = 0;
                $totalTax = 0;

                foreach ($selectedItems as $item) {
                    $quantity = rand(1, 5);
                    $unitPrice = $item->price;
                    $discountRate = rand(0, 10);
                    
                    $lineTotal = $quantity * $unitPrice;
                    $discountAmount = ($lineTotal * $discountRate) / 100;
                    $afterDiscount = $lineTotal - $discountAmount;
                    $taxAmount = ($afterDiscount * $item->tax_rate) / 100;
                    $lineTotalWithTax = $afterDiscount + $taxAmount;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_rate' => $discountRate,
                        'discount_amount' => $discountAmount,
                        'tax_rate' => $item->tax_rate,
                        'tax_amount' => $taxAmount,
                        'line_total' => $lineTotalWithTax,
                    ]);

                    $subtotal += $afterDiscount;
                    $totalTax += $taxAmount;
                }

                // Update invoice totals
                $invoice->update([
                    'subtotal' => $subtotal,
                    'sales_tax' => $totalTax,
                    'total_amount' => $subtotal + $totalTax,
                ]);
            }
        }
    }
}