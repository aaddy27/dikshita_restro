<?php

namespace App\Livewire\Admin;

use App\Models\Review;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaManager extends Component
{
    use WithFileUploads;

    // Hero Video State
    public $heroVideoFile = null;
    public ?string $heroVideoPath = null;

    // 7 Slider Images State
    public array $sliderImages = [];
    public array $newSliderImages = []; // [slot_index => UploadedFile]
    public array $slotTitles = [];
    public array $slotTags = [];

    // Notification / Flash
    public ?string $statusMessage = null;

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->heroVideoPath = SiteSetting::get('hero_video_path');

        $defaultSlider = [
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
        ];

        $this->sliderImages = SiteSetting::get('slider_images', $defaultSlider);

        // Ensure 7 slots
        while (count($this->sliderImages) < 7) {
            $this->sliderImages[] = [
                'url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop&q=80',
                'title' => 'Signature Specialty ' . (count($this->sliderImages) + 1),
                'tag' => 'Chef Recommendation',
            ];
        }

        foreach ($this->sliderImages as $idx => $slot) {
            $this->slotTitles[$idx] = $slot['title'] ?? ('Slide ' . ($idx + 1));
            $this->slotTags[$idx] = $slot['tag'] ?? 'Featured Dish';
        }
    }

    public function uploadHeroVideo()
    {
        $this->validate([
            'heroVideoFile' => 'required|file|mimes:mp4,webm,mov,ogg|max:51200', // max 50MB
        ]);

        // Delete old video if exists
        if ($this->heroVideoPath && Storage::disk('public')->exists($this->heroVideoPath)) {
            Storage::disk('public')->delete($this->heroVideoPath);
        }

        $extension = $this->heroVideoFile->getClientOriginalExtension();
        $fileName = 'hero_video_' . time() . '.' . $extension;
        $path = $this->heroVideoFile->storeAs('media', $fileName, 'public');

        SiteSetting::set('hero_video_path', $path);
        $this->heroVideoPath = $path;
        $this->heroVideoFile = null;

        $this->statusMessage = 'Hero background video successfully uploaded and replaced!';
    }

    public function removeHeroVideo()
    {
        if ($this->heroVideoPath && Storage::disk('public')->exists($this->heroVideoPath)) {
            Storage::disk('public')->delete($this->heroVideoPath);
        }

        SiteSetting::set('hero_video_path', null);
        $this->heroVideoPath = null;
        $this->statusMessage = 'Custom video removed. Reverted to default restaurant video loop.';
    }

    public function uploadSliderImage(int $index)
    {
        if (!isset($this->newSliderImages[$index])) {
            return;
        }

        $this->validate([
            "newSliderImages.{$index}" => 'required|image|mimes:jpg,jpeg,png,webp|max:10240', // max 10MB
        ]);

        $file = $this->newSliderImages[$index];
        $fileName = 'slider_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('media', $fileName, 'public');

        $this->sliderImages[$index]['url'] = asset('storage/' . $path);
        $this->sliderImages[$index]['title'] = $this->slotTitles[$index] ?? ('Slide ' . ($index + 1));
        $this->sliderImages[$index]['tag'] = $this->slotTags[$index] ?? 'Featured';

        SiteSetting::set('slider_images', $this->sliderImages);
        unset($this->newSliderImages[$index]);

        $this->statusMessage = "Slide #{$index} image replaced successfully!";
    }

    public function updateSliderSlotMeta(int $index)
    {
        $this->sliderImages[$index]['title'] = $this->slotTitles[$index] ?? ('Slide ' . ($index + 1));
        $this->sliderImages[$index]['tag'] = $this->slotTags[$index] ?? 'Featured';

        SiteSetting::set('slider_images', $this->sliderImages);
        $this->statusMessage = "Slide #{$index} details updated!";
    }

    public function toggleReviewApproval(int $id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => !$review->is_approved]);
        $this->statusMessage = "Review updated: " . ($review->is_approved ? 'Approved and Visible on Website' : 'Hidden from Website');
    }

    public function deleteReview(int $id)
    {
        Review::findOrFail($id)->delete();
        $this->statusMessage = 'Review deleted successfully.';
    }

    public function render()
    {
        $reviews = Review::latest()->paginate(15);

        return view('livewire.admin.media-manager', [
            'reviews' => $reviews,
        ])->layout('layouts.app', ['title' => 'Website Media & Reviews - Dikshita Restro Admin']);
    }
}
