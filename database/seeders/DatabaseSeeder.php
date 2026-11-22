<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DiscountRule;
use App\Models\ItemVariant;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\OrderCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Staff Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@restro.com'],
            [
                'name' => 'Dikshita Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'security_pin' => '9999',
                'phone' => '9876543210',
            ]
        );

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@restro.com'],
            [
                'name' => 'Rahul Cashier',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'security_pin' => '1234',
                'phone' => '9876543211',
            ]
        );

        $waiter = User::updateOrCreate(
            ['email' => 'waiter@restro.com'],
            [
                'name' => 'Amit Waiter',
                'password' => Hash::make('password'),
                'role' => 'waiter',
                'security_pin' => null,
                'phone' => '9876543212',
            ]
        );

        $cook = User::updateOrCreate(
            ['email' => 'cook@restro.com'],
            [
                'name' => 'Head Chef (Cook)',
                'password' => Hash::make('password'),
                'role' => 'cook',
                'security_pin' => '5555',
                'phone' => '9876543213',
            ]
        );

        // 2. Create Restaurant Tables
        $tables = [];
        for ($i = 1; $i <= 8; $i++) {
            $tableNumber = 'T-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $tables[$i] = RestaurantTable::updateOrCreate(
                ['table_number' => $tableNumber],
                [
                    'capacity' => ($i % 3 === 0) ? 6 : (($i % 2 === 0) ? 2 : 4),
                    'qr_token' => (string) Str::uuid(),
                    'status' => 'vacant',
                ]
            );
        }

        // 3. Create Categories
        $catPizza = Category::updateOrCreate(['name' => 'Gourmet Pizzas & Tandoori Specials'], ['sort_order' => 1, 'is_active' => true]);
        $catStarters = Category::updateOrCreate(['name' => 'Starters & Appetizers'], ['sort_order' => 2, 'is_active' => true]);
        $catMain = Category::updateOrCreate(['name' => 'Main Course'], ['sort_order' => 3, 'is_active' => true]);
        $catBreads = Category::updateOrCreate(['name' => 'Breads & Rice'], ['sort_order' => 4, 'is_active' => true]);
        $catBeverages = Category::updateOrCreate(['name' => 'Beverages & Desserts'], ['sort_order' => 5, 'is_active' => true]);

        // Screenshot Special Items
        $mPizza1 = MenuItem::updateOrCreate(
            ['name' => 'Tandoori Tikka & Smoked Peppers', 'category_id' => $catPizza->id],
            [
                'description' => 'Char-grilled succulent tandoori tikka chunks paired with smoked bell peppers, red paprika, and artisanal mozzarella on hand-tossed crust.',
                'type' => 'non_veg',
                'base_price' => 545.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );
        ItemVariant::updateOrCreate(['menu_item_id' => $mPizza1->id, 'name' => 'Regular (8")'], ['price' => 545.00]);
        ItemVariant::updateOrCreate(['menu_item_id' => $mPizza1->id, 'name' => 'Large (12")'], ['price' => 795.00]);

        $mPizza2 = MenuItem::updateOrCreate(
            ['name' => 'World Of Veggies', 'category_id' => $catPizza->id],
            [
                'description' => 'Loaded garden-fresh broccoli, crisp baby corn, sun-ripened tomatoes, black olives, and gourmet cheese blend.',
                'type' => 'veg',
                'base_price' => 545.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        $mPizza3 = MenuItem::updateOrCreate(
            ['name' => 'Super Hot Spot', 'category_id' => $catPizza->id],
            [
                'description' => 'Spicy peri-peri seasoned crust topped with pickled jalapeños, crispy capsicum, fiery ghost chilli drizzle, and melted cheddar.',
                'type' => 'veg',
                'base_price' => 515.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        // 4. Create Menu Items & Variants
        // Item 1: Paneer Tikka (Variants: Half, Full)
        $m1 = MenuItem::updateOrCreate(
            ['name' => 'Paneer Tikka', 'category_id' => $catStarters->id],
            [
                'description' => 'Cottage cheese marinated in tandoori spices and cooked in clay oven.',
                'type' => 'veg',
                'base_price' => 180.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );
        ItemVariant::updateOrCreate(['menu_item_id' => $m1->id, 'name' => 'Half (4 Pcs)'], ['price' => 160.00]);
        ItemVariant::updateOrCreate(['menu_item_id' => $m1->id, 'name' => 'Full (8 Pcs)'], ['price' => 280.00]);

        // Item 2: Chicken Tikka (Variants: Half, Full)
        $m2 = MenuItem::updateOrCreate(
            ['name' => 'Murgh Tikka Angara', 'category_id' => $catStarters->id],
            [
                'description' => 'Succulent boneless chicken chunks infused with fiery Kashmiri red chilli.',
                'type' => 'non_veg',
                'base_price' => 240.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );
        ItemVariant::updateOrCreate(['menu_item_id' => $m2->id, 'name' => 'Half (4 Pcs)'], ['price' => 220.00]);
        ItemVariant::updateOrCreate(['menu_item_id' => $m2->id, 'name' => 'Full (8 Pcs)'], ['price' => 380.00]);

        // Item 3: Crispy Corn Chilli Pepper (No variants)
        $m3 = MenuItem::updateOrCreate(
            ['name' => 'Crispy Corn Salt & Pepper', 'category_id' => $catStarters->id],
            [
                'description' => 'Golden fried sweet corn tossed with spring onion and crushed pepper.',
                'type' => 'veg',
                'base_price' => 210.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        // Item 4: Dal Makhani
        $m4 = MenuItem::updateOrCreate(
            ['name' => 'Dal Makhani Bukhara', 'category_id' => $catMain->id],
            [
                'description' => 'Slow cooked black lentils simmered overnight with cream and churned butter.',
                'type' => 'veg',
                'base_price' => 260.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        // Item 5: Butter Chicken
        $m5 = MenuItem::updateOrCreate(
            ['name' => 'Delhi Butter Chicken', 'category_id' => $catMain->id],
            [
                'description' => 'Charcoal roasted chicken folded into rich velvety tomato cashew gravy.',
                'type' => 'non_veg',
                'base_price' => 320.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );
        ItemVariant::updateOrCreate(['menu_item_id' => $m5->id, 'name' => 'Half'], ['price' => 260.00]);
        ItemVariant::updateOrCreate(['menu_item_id' => $m5->id, 'name' => 'Full'], ['price' => 440.00]);

        // Item 6: Butter Naan
        $m6 = MenuItem::updateOrCreate(
            ['name' => 'Butter Garlic Naan', 'category_id' => $catBreads->id],
            [
                'description' => 'Refined flour bread topped with minced garlic and butter.',
                'type' => 'veg',
                'base_price' => 60.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        // Item 7: Hyderabadi Dum Biryani
        $m7 = MenuItem::updateOrCreate(
            ['name' => 'Dum Biryani with Salan', 'category_id' => $catBreads->id],
            [
                'description' => 'Aromatic basmati rice cooked on slow flame with saffron and whole spices.',
                'type' => 'non_veg',
                'base_price' => 340.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );
        ItemVariant::updateOrCreate(['menu_item_id' => $m7->id, 'name' => 'Veg'], ['price' => 240.00]);
        ItemVariant::updateOrCreate(['menu_item_id' => $m7->id, 'name' => 'Chicken'], ['price' => 340.00]);
        ItemVariant::updateOrCreate(['menu_item_id' => $m7->id, 'name' => 'Mutton'], ['price' => 450.00]);

        // Item 8: Mineral Water (Excluded from discounts)
        $m8 = MenuItem::updateOrCreate(
            ['name' => 'Packaged Mineral Water (1L)', 'category_id' => $catBeverages->id],
            [
                'description' => 'Packaged drinking water bottle. Excluded from promotional discounts.',
                'type' => 'veg',
                'base_price' => 30.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => true, // EXCLUDED
                'is_available' => true,
            ]
        );

        // Item 9: Sizzling Walnut Brownie
        $m9 = MenuItem::updateOrCreate(
            ['name' => 'Sizzling Brownie with Ice Cream', 'category_id' => $catBeverages->id],
            [
                'description' => 'Warm fudgy brownie served on sizzler plate with vanilla scoop & hot chocolate.',
                'type' => 'veg',
                'base_price' => 190.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        // Item 10: Fresh Mint Mojito
        $m10 = MenuItem::updateOrCreate(
            ['name' => 'Fresh Mint Virgin Mojito', 'category_id' => $catBeverages->id],
            [
                'description' => 'Refreshing cooler with crushed mint, lime chunks, and sparkling soda.',
                'type' => 'veg',
                'base_price' => 130.00,
                'gst_percentage' => 5.00,
                'is_discount_excluded' => false,
                'is_available' => true,
            ]
        );

        // 5. Discount Rules
        // Default Wednesday rule as requested
        DiscountRule::updateOrCreate(
            ['name' => 'Wednesday Feast 15% Off'],
            [
                'rule_type' => 'weekday',
                'day_of_week' => 'wednesday',
                'specific_date' => null,
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'min_order_amount' => 300.00,
                'is_active' => true,
            ]
        );

        // Active rule for today's weekday so user immediately sees day discounts working
        $currentDay = strtolower(Carbon::now()->format('l'));
        if ($currentDay !== 'wednesday') {
            DiscountRule::updateOrCreate(
                ['name' => ucfirst($currentDay) . ' Special 10% Off'],
                [
                    'rule_type' => 'weekday',
                    'day_of_week' => $currentDay,
                    'specific_date' => null,
                    'discount_type' => 'percentage',
                    'discount_value' => 10.00,
                    'min_order_amount' => 200.00,
                    'is_active' => true,
                ]
            );
        }

        // 6. Create Live Sample Orders for Demo
        $calcService = new OrderCalculationService();

        // Sample Order 1 on Table 1: Occupied & Open
        $table1 = $tables[1];
        $order1 = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(6)),
            'table_id' => $table1->id,
            'status' => 'open',
            'customer_name' => 'Dev Sharma',
            'customer_phone' => '9898012345',
            'notes' => 'Less spicy in Dal Makhani please.',
        ]);

        $item1_1 = OrderItem::create([
            'order_id' => $order1->id,
            'menu_item_id' => $m1->id,
            'item_variant_id' => $m1->variants->first()->id,
            'item_name' => $m1->name,
            'variant_name' => $m1->variants->first()->name,
            'qty' => 1,
            'unit_price' => 160.00,
            'total_price' => 160.00,
            'kot_status' => 'printed',
            'added_by_type' => 'customer',
        ]);

        $item1_2 = OrderItem::create([
            'order_id' => $order1->id,
            'menu_item_id' => $m4->id,
            'item_variant_id' => null,
            'item_name' => $m4->name,
            'variant_name' => null,
            'qty' => 1,
            'unit_price' => 260.00,
            'total_price' => 260.00,
            'kot_status' => 'pending', // Pending KOT!
            'added_by_type' => 'waiter',
            'added_by_id' => $waiter->id,
        ]);

        $item1_3 = OrderItem::create([
            'order_id' => $order1->id,
            'menu_item_id' => $m6->id,
            'item_variant_id' => null,
            'item_name' => $m6->name,
            'variant_name' => null,
            'qty' => 2,
            'unit_price' => 60.00,
            'total_price' => 120.00,
            'kot_status' => 'pending', // Pending KOT!
            'added_by_type' => 'customer',
        ]);

        $calcService->syncOrderTotals($order1);
        $table1->update([
            'status' => 'occupied',
            'current_order_id' => $order1->id,
        ]);

        // Sample Order 2 on Table 2: Locked Bill Demo
        $table2 = $tables[2];
        $order2 = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(6)),
            'table_id' => $table2->id,
            'status' => 'locked',
            'customer_name' => 'Priya Patel',
            'customer_phone' => '9876509876',
            'locked_by' => $cashier->id,
            'locked_at' => now()->subMinutes(5),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_item_id' => $m2->id,
            'item_variant_id' => $m2->variants->last()->id,
            'item_name' => $m2->name,
            'variant_name' => $m2->variants->last()->name,
            'qty' => 1,
            'unit_price' => 380.00,
            'total_price' => 380.00,
            'kot_status' => 'printed',
            'added_by_type' => 'waiter',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_item_id' => $m7->id,
            'item_variant_id' => $m7->variants->where('name', 'Chicken')->first()->id,
            'item_name' => $m7->name,
            'variant_name' => 'Chicken',
            'qty' => 2,
            'unit_price' => 340.00,
            'total_price' => 680.00,
            'kot_status' => 'printed',
            'added_by_type' => 'customer',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_item_id' => $m8->id, // Mineral water - excluded
            'item_variant_id' => null,
            'item_name' => $m8->name,
            'variant_name' => null,
            'qty' => 2,
            'unit_price' => 30.00,
            'total_price' => 60.00,
            'kot_status' => 'printed',
            'added_by_type' => 'waiter',
        ]);

        $calcService->syncOrderTotals($order2);
        $table2->update([
            'status' => 'locked',
            'current_order_id' => $order2->id,
        ]);

        // Audit Log for Lock
        AuditLog::record(
            $order2->id,
            'bill_locked',
            null,
            ['status' => 'locked'],
            'Bill locked by cashier for customer checkout & payment preparation.',
            $cashier->id
        );

        // 7. Seed Initial Customer Reviews
        $reviews = [
            [
                'customer_name' => 'Rajesh Sharma',
                'customer_phone' => '9876543210',
                'rating' => 5,
                'comment' => 'The Bukhara Dal Makhani and Tandoori Tikka were absolute perfection! The table QR ordering was so smooth, food arrived piping hot without waiting.',
                'favorite_dish' => 'Bukhara Dal Makhani & Garlic Naan',
                'is_approved' => true,
            ],
            [
                'customer_name' => 'Ananya Verma',
                'customer_phone' => '9876543211',
                'rating' => 5,
                'comment' => 'Exceptional family dinner! Ambiance is warm, staff is attentive, and the World of Veggies gourmet pizza had the freshest toppings we have ever tasted.',
                'favorite_dish' => 'World Of Veggies Pizza',
                'is_approved' => true,
            ],
            [
                'customer_name' => 'Vikram Malhotra',
                'customer_phone' => '9876543212',
                'rating' => 5,
                'comment' => 'Top tier dining experience in the tech zone. Loved the charcoal aroma from the live tandoor and the artisanal Dum Biryani. 10/10!',
                'favorite_dish' => 'Hyderabadi Dum Biryani',
                'is_approved' => true,
            ],
            [
                'customer_name' => 'Priya Nair',
                'customer_phone' => '9876543213',
                'rating' => 5,
                'comment' => 'Wonderful mocktails and succulent Paneer Malai Tikka. The digital QR ordering at the table makes contactless dining a breeze.',
                'favorite_dish' => 'Paneer Malai Tikka & Mojito',
                'is_approved' => true,
            ],
        ];

        foreach ($reviews as $rev) {
            \App\Models\Review::updateOrCreate(
                ['customer_name' => $rev['customer_name']],
                $rev
            );
        }

        // 8. Seed Default 7 Slider Images in SiteSettings if not set
        if (!\App\Models\SiteSetting::get('slider_images')) {
            \App\Models\SiteSetting::set('slider_images', [
                [
                    'url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Smoky Charcoal Tandoor Grills',
                    'tag' => 'Clay Oven Specialty',
                ],
                [
                    'url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Handcrafted Gourmet Pizzas',
                    'tag' => 'Fresh Dough & Artisanal Cheese',
                ],
                [
                    'url' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Slow-Simmered Heritage Gravies',
                    'tag' => '24-Hr Cooked Dal Makhani',
                ],
                [
                    'url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Royal Fragrant Dum Biryani',
                    'tag' => 'Sealed In Earthen Handi',
                ],
                [
                    'url' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Crisp Farm-Fresh Starters',
                    'tag' => '100% Pure & Organic',
                ],
                [
                    'url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Artisanal Handcrafted Mocktails',
                    'tag' => 'Chilled & Refreshing',
                ],
                [
                    'url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop&q=80',
                    'title' => 'Chic Fine-Dining Ambiance',
                    'tag' => 'Memorable Hospitality',
                ],
            ]);
        }
    }
}
