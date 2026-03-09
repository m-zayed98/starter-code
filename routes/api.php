<?php

use App\Facades\MediaUpload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/upload-image', function (Request $request) {

    // dd($request->all());
    // dd(User::find(1));
    $media = MediaUpload::file($request->file('avatar'))
        ->collection('avatars')
        ->name('avatar_1')
        ->uploadTo(User::find(1));

    return response()->json(['avatar_url' => $media->getUrl()]);
});
