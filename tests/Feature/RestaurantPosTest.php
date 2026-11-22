<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\OrderCalculationService;
use App\Services\WhatsAppInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantPosTest extends TestCase
{
    public function test_customer_can_access_table_qr_ordering_publicly(): void
    {
        $table = RestaurantTable::first();
        $this->assertNotNull($table);

        $response = $this->get(route('table.qr', $table->qr_token));
        $response->assertStatus(200);
        $response->assertSee($table->table_number);
    }

    public function test_cashier_can_access_master_pos(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $this->assertNotNull($cashier);

        $response = $this->actingAs($cashier)->get(route('cashier.pos'));
        $response->assertStatus(200);
        $response->assertSee('Cashier Master POS');
    }

    public function test_waiter_can_access_waiter_pos(): void
    {
        $waiter = User::where('role', 'waiter')->first();
        $this->assertNotNull($waiter);

        $response = $this->actingAs($waiter)->get(route('waiter.pos'));
        $response->assertStatus(200);
        $response->assertSee('Waiter Table Map');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Executive POS Dashboard');
    }

    public function test_order_calculation_service_accuracy(): void
    {
        $calcService = new OrderCalculationService();

        // Sample items:
        // Item 1: 2 x 100 = 200 (eligible)
        // Item 2: 1 x 50 = 50 (excluded)
        $items = [
            ['qty' => 2, 'unit_price' => 100.0, 'is_discount_excluded' => false],
            ['qty' => 1, 'unit_price' => 50.0, 'is_discount_excluded' => true],
        ];

        // 1. Percentage manual discount of 10%
        $result = $calcService->calculate($items, 'percentage', 10.0);
        $this->assertEquals(250.00, $result['subtotal']);
        $this->assertEquals(200.00, $result['eligible_subtotal']);
        $this->assertEquals(50.00, $result['excluded_subtotal']);

        // 2. Manual override: target final payable amount of 200
        $overrideResult = $calcService->calculate($items, 'manual_override', 200.0);
        $this->assertEquals(200.00, $overrideResult['final_payable_amount']);
        $this->assertGreaterThan(0, $overrideResult['manual_discount_amount']);
        $this->assertEquals(round($overrideResult['tax_amount'] / 2, 2), $overrideResult['cgst_amount']);
    }

    public function test_thermal_receipt_renders(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $response = $this->get(route('order.receipt', $order->id));
        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('80mm');
    }

    public function test_whatsapp_invoice_service_generates_share_url(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $service = new WhatsAppInvoiceService();
        $shareUrl = $service->getWhatsAppShareUrl($order, '9876543210');

        $this->assertStringContainsString('https://wa.me/919876543210', $shareUrl);
        $this->assertStringContainsString(urlencode($order->order_number), $shareUrl);
    }

    public function test_upi_qr_and_payment_url_generation_for_final_amount(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $defaultUrl = $order->getUpiPaymentUrl();
        $this->assertStringContainsString('upi://pay?', $defaultUrl);
        $this->assertStringContainsString('cu=INR', $defaultUrl);

        // Test custom finalized amount override
        $customOverrideUrl = $order->getUpiPaymentUrl(1100.00);
        $this->assertStringContainsString('am=1100.00', $customOverrideUrl);

        // Test SVG QR Code generation
        $svg = $order->getUpiQrSvg(120, 1100.00);
        $this->assertStringContainsString('<svg', $svg);

        // Test receipt includes UPI QR
        $response = $this->get(route('order.receipt', $order->id));
        $response->assertStatus(200);
        $response->assertSee('SCAN & PAY VIA ANY UPI APP', false);
    }

    public function test_download_pdf_generates_80mm_thermal_receipt_pdf(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $response = $this->get(route('order.pdf', $order->id));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        $service = new WhatsAppInvoiceService();
        $pdfPath = $service->generatePdf($order);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($pdfPath));

        $content = \Illuminate\Support\Facades\Storage::disk('public')->get($pdfPath);
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_website_clean_user_facing_and_reviews(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // Ensure admin login buttons are removed from public website
        $response->assertDontSee('POS Dashboard &rarr;', false);
        $response->assertDontSee('Staff Login');

        // Ensure Review button and QR Scanner buttons are present
        $response->assertSee('Write a Review');
        $response->assertSee('Scan Table QR Code');

        // Test review submission
        $reviewResponse = $this->post(route('reviews.store'), [
            'customer_name' => 'Karan Patel',
            'rating' => 5,
            'comment' => 'Fantastic authentic cuisine and lightning fast service!',
            'favorite_dish' => 'Dal Bukhara',
        ]);

        $reviewResponse->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'customer_name' => 'Karan Patel',
            'rating' => 5,
        ]);
    }

    public function test_admin_can_access_media_manager(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get(route('admin.media'));
        $response->assertStatus(200);
        $response->assertSee('Website Media, Slider &amp; Reviews', false);
    }

    public function test_admin_can_upload_and_replace_hero_video(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $video1 = \Illuminate\Http\UploadedFile::fake()->create('restaurant_video_1.mp4', 5000, 'video/mp4');

        $response1 = $this->actingAs($admin)->post(route('admin.media.video.upload'), [
            'video' => $video1,
        ]);

        $response1->assertRedirect();
        $path1 = \App\Models\SiteSetting::get('hero_video_path');
        $this->assertNotNull($path1);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($path1));

        // Test replacement with second video
        $video2 = \Illuminate\Http\UploadedFile::fake()->create('restaurant_video_2.mp4', 8000, 'video/mp4');

        $response2 = $this->actingAs($admin)->post(route('admin.media.video.upload'), [
            'video' => $video2,
        ]);

        $response2->assertRedirect();
        $path2 = \App\Models\SiteSetting::get('hero_video_path');
        $this->assertNotNull($path2);
        $this->assertNotEquals($path1, $path2);
        // Previous video was deleted/replaced
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($path1));
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($path2));

        // Test remove/reset
        $response3 = $this->actingAs($admin)->post(route('admin.media.video.remove'));
        $response3->assertRedirect();
        $this->assertNull(\App\Models\SiteSetting::get('hero_video_path'));
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($path2));
    }

    public function test_cook_can_access_kitchen_display(): void
    {
        $cook = User::where('role', 'cook')->first();
        $this->assertNotNull($cook);

        $response = $this->actingAs($cook)->get(route('kitchen.display'));
        $response->assertStatus(200);
        $response->assertSee('Kitchen Order Display');
        $response->assertSee('Items to Cook');

        // Test Livewire component reactivity
        \Livewire\Livewire::actingAs($cook)
            ->test(\App\Livewire\Kitchen\KitchenDisplay::class)
            ->assertViewHas('orders')
            ->assertViewHas('totalItemsToCook')
            ->call('setFilter', 'veg')
            ->assertSet('filter', 'veg');
    }

    public function test_admin_can_upload_dish_image_in_menu(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = User::where('role', 'admin')->first();
        $item = \App\Models\MenuItem::first();

        $image = \Illuminate\Http\UploadedFile::fake()->image('dal_makhani.jpg', 600, 600);

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\MenuManager::class)
            ->call('editItem', $item->id)
            ->set('image', $image)
            ->call('saveItem');

        $item->refresh();
        $this->assertNotNull($item->image_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($item->image_path);
        $this->assertNotNull($item->image_url);
    }
}
