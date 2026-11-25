<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update any existing non-veg items to delicious pure veg counterparts
        DB::table('menu_items')->where('name', 'Tandoori Tikka & Smoked Peppers')->update([
            'name' => 'Paneer Tikka & Smoked Peppers Pizza',
            'description' => 'Char-grilled succulent cottage cheese cubes paired with smoked bell peppers, red paprika, and artisanal mozzarella on hand-tossed crust.',
            'type' => 'veg',
        ]);

        DB::table('menu_items')->where('name', 'Murgh Tikka Angara')->update([
            'name' => 'Tandoori Soya Chaap Angara',
            'description' => 'Succulent tandoori soya chaap chunks marinated in rich tandoori spices and charred in clay oven.',
            'type' => 'veg',
        ]);

        DB::table('menu_items')->where('name', 'Delhi Butter Chicken')->update([
            'name' => 'Shahi Paneer Butter Masala',
            'description' => 'Fresh tender cottage cheese cubes folded into rich velvety tomato cashew gravy and churned white butter.',
            'type' => 'veg',
        ]);

        DB::table('menu_items')->where('name', 'Dum Biryani with Salan')->update([
            'name' => 'Shahi Veg Dum Biryani with Salan',
            'description' => 'Aromatic long-grain basmati rice cooked on slow flame with seasonal vegetables, fresh paneer, saffron and whole spices.',
            'type' => 'veg',
        ]);

        // Ensure absolutely all items have type = 'veg'
        DB::table('menu_items')->where('type', '!=', 'veg')->update(['type' => 'veg']);

        // Update item variants if any reference non-veg names
        DB::table('item_variants')->where('name', 'Chicken')->update(['name' => 'Full Handi', 'price' => 280.00]);
        DB::table('item_variants')->where('name', 'Mutton')->delete();
        DB::table('item_variants')->where('name', 'Veg')->update(['name' => 'Half Handi', 'price' => 180.00]);
    }

    public function down(): void
    {
        // Reversible if needed
    }
};
