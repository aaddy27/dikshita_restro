<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\ItemVariant;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MenuManager extends Component
{
    use WithFileUploads;

    // Item Form
    public bool $showItemModal = false;
    public ?int $editingItemId = null;
    public string $name = '';
    public ?int $category_id = null;
    public string $description = '';
    public $image = null;
    public ?string $existingImagePath = null;
    public string $type = 'veg'; // 100% Pure Vegetarian restaurant
    public float $base_price = 0.0;
    public float $gst_percentage = 5.0;
    public bool $is_discount_excluded = false;
    public bool $is_available = true;

    // Variants Management Modal
    public bool $showVariantModal = false;
    public ?MenuItem $selectedItemForVariants = null;
    public string $variantName = '';
    public float $variantPrice = 0.0;

    // Category Form Modal
    public bool $showCategoryModal = false;
    public string $newCategoryName = '';

    // Active Category Filter
    public ?int $activeCategoryFilter = null;

    public function openCreateItem()
    {
        $this->reset(['editingItemId', 'name', 'description', 'base_price', 'image', 'existingImagePath']);
        $this->type = 'veg';
        $this->gst_percentage = 5.0;
        $this->is_discount_excluded = false;
        $this->is_available = true;
        $this->category_id = Category::first()?->id;
        $this->showItemModal = true;
    }

    public function editItem(int $id)
    {
        $item = MenuItem::findOrFail($id);
        $this->editingItemId = $item->id;
        $this->name = $item->name;
        $this->category_id = $item->category_id;
        $this->description = $item->description ?? '';
        $this->existingImagePath = $item->image_path;
        $this->image = null;
        $this->type = $item->type;
        $this->base_price = (float) $item->base_price;
        $this->gst_percentage = (float) $item->gst_percentage;
        $this->is_discount_excluded = (bool) $item->is_discount_excluded;
        $this->is_available = (bool) $item->is_available;
        $this->showItemModal = true;
    }

    public function removeDishImage()
    {
        if ($this->editingItemId) {
            $item = MenuItem::find($this->editingItemId);
            if ($item && $item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
                $item->update(['image_path' => null]);
            }
        }
        $this->image = null;
        $this->existingImagePath = null;
    }

    public function saveItem()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'gst_percentage' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', // max 5MB
        ]);

        $imagePath = $this->existingImagePath;

        if ($this->image) {
            // Delete old image if replacing
            if ($this->existingImagePath && Storage::disk('public')->exists($this->existingImagePath)) {
                Storage::disk('public')->delete($this->existingImagePath);
            }
            $imagePath = $this->image->store('dishes', 'public');
        }

        MenuItem::updateOrCreate(
            ['id' => $this->editingItemId],
            [
                'name' => $this->name,
                'category_id' => $this->category_id,
                'description' => $this->description,
                'image_path' => $imagePath,
                'type' => $this->type,
                'base_price' => $this->base_price,
                'gst_percentage' => $this->gst_percentage,
                'is_discount_excluded' => $this->is_discount_excluded,
                'is_available' => $this->is_available,
            ]
        );

        $this->showItemModal = false;
        $this->reset(['image', 'existingImagePath']);
        session()->flash('message', 'Menu Item saved successfully with image.');
    }

    public function toggleAvailability(int $id)
    {
        $item = MenuItem::findOrFail($id);
        $item->is_available = !$item->is_available;
        $item->save();
    }

    public function deleteItem(int $id)
    {
        MenuItem::findOrFail($id)->delete();
        session()->flash('message', 'Menu item removed.');
    }

    // Variants Management
    public function manageVariants(int $id)
    {
        $this->selectedItemForVariants = MenuItem::with('variants')->findOrFail($id);
        $this->variantName = '';
        $this->variantPrice = 0.0;
        $this->showVariantModal = true;
    }

    public function addVariant()
    {
        $this->validate([
            'variantName' => 'required|string',
            'variantPrice' => 'required|numeric|min:1',
        ]);

        ItemVariant::create([
            'menu_item_id' => $this->selectedItemForVariants->id,
            'name' => $this->variantName,
            'price' => $this->variantPrice,
        ]);

        $this->variantName = '';
        $this->variantPrice = 0.0;
        $this->selectedItemForVariants->load('variants');
    }

    public function deleteVariant(int $id)
    {
        ItemVariant::findOrFail($id)->delete();
        $this->selectedItemForVariants->load('variants');
    }

    // Category Creation
    public function saveCategory()
    {
        $this->validate([
            'newCategoryName' => 'required|string|unique:categories,name',
        ]);

        Category::create([
            'name' => $this->newCategoryName,
            'sort_order' => Category::count() + 1,
            'is_active' => true,
        ]);

        $this->newCategoryName = '';
        $this->showCategoryModal = false;
        session()->flash('message', 'Category created successfully.');
    }

    public function render()
    {
        $categories = Category::withCount('menuItems')->orderBy('sort_order')->get();

        $query = MenuItem::with(['category', 'variants']);
        if ($this->activeCategoryFilter) {
            $query->where('category_id', $this->activeCategoryFilter);
        }
        $items = $query->orderBy('name')->get();

        return view('livewire.admin.menu-manager', compact('categories', 'items'))
            ->layout('layouts.app', ['title' => 'Menu & Variants Management - Dikshita Restro']);
    }
}
