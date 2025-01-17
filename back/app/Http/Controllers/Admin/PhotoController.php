<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    /**
     * Свободная загрузка.
     * Используется в загрузке фоток в инструкции
     */
    public function __invoke(Request $request)
    {
        $name = Photo::arbitraryUpload($request->file('photo'));
        return [
            'name' => $name
        ];
    }
}
