<?php

namespace App\Http\Controllers;

use Intervention\Image\Laravel\Facades\Image;

class AvatarController extends Controller
{
    public function generateAvatar($name)
    {
        // Extract initials from name
        // $initials = trim(collect(explode(' ', $name))->map(function ($segment) {
        //     return mb_substr($segment, 0, 1);
        // })->join(' '));
        $initials = collect(explode(' ', $name))->map(fn ($part) => strtoupper($part[0]))->join('');

        // Create avatar
        $image = Image::create(500, 500)->fill('#cef2ef')
            ->text($initials, 250, 250, function ($font) {
                $font->file(public_path('webfonts/Tinos-Regular.ttf')); // Font path
                $font->size(230);
                $font->color('#009e5c');
                $font->stroke('#cef2ef', 1);
                $font->align('center');
                $font->valign('middle');
                $font->lineHeight(1.6);
                $font->angle(0);
                $font->wrap(250);
            });

        // Save to local file
        $fileName = 'images/avatars/'.md5($name).'.png';
        $image->save(public_path($fileName));

        // Return image URL
        return asset($fileName);
    }
}
