<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updatePhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ]);

        $photo = $validated['photo'];
        $request->user()->update([
            'profile_photo_data' => base64_encode($photo->getContent()),
            'profile_photo_mime' => $photo->getMimeType(),
        ]);

        return back()->with([
            'profile_photo_saved' => true,
            'profile_settings_open' => true,
        ]);
    }
}