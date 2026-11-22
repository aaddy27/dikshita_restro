<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function uploadHeroVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mov,ogg,avi,mkv|max:102400', // 100MB
        ], [
            'video.required' => 'Kripya ek video file select karein.',
            'video.mimes' => 'Video MP4, WebM, MOV, OGG ya MKV format mein honi chahiye.',
            'video.max' => 'Video size 100MB se kam hona chahiye.',
        ]);

        $file = $request->file('video');

        // Delete old video if exists
        $oldPath = SiteSetting::get('hero_video_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $extension = $file->getClientOriginalExtension();
        $fileName = 'hero_video_' . time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs('media', $fileName, 'public');

        SiteSetting::set('hero_video_path', $path);

        return back()->with('status', 'Video safaltapoorvak upload aur replace ho gayi hai!');
    }

    public function removeHeroVideo()
    {
        $oldPath = SiteSetting::get('hero_video_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        SiteSetting::set('hero_video_path', null);

        return back()->with('status', 'Custom video delete kar di gayi hai. Default video active hai.');
    }
}
