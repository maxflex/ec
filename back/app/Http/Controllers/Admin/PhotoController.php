<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'file'],
            'entity_type' => ['required', 'string'],
            'entity_id' => ['required']
        ]);
        // remove old photo if exists, with file
        Photo::query()
            ->where('entity_type', $request->entity_type)
            ->where('entity_id', $request->entity_id)
            ->first()?->delete();
        $photo = Photo::create($request->all());
        $photo->upload($request->file('photo'));
        return [
            'photo_url' => $photo->url
        ];
    }

    /**
     * Свободная загрузка.
     * Используется в загрузке фоток в инструкции
     */
    public function upload(Request $request)
    {
        $name = Photo::arbitraryUpload($request->file('photo'));
        return [
            'name' => $name
        ];
    }
}
