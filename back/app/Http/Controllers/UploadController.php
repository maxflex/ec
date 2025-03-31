<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Загрузка файлов (тесты, домашние задания, раздаточные материалы)
     */
    public function files(Request $request)
    {
        $request->validate([
            'file' => ['file', 'max:10240'], // 10 MB
            'folder' => ['required', 'string'],
        ]);
        $file = $request->file('file');
        $folder = $request->input('folder');
        $fileName = uniqid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('crm/'.$folder, $fileName);

        return cdn($folder, $fileName);
    }

    /**
     * Загрузка фото (аватары)
     */
    public function photos(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'file'],
            'entity_type' => ['required', 'string'],
            'entity_id' => ['required'],
        ]);
        // remove old photo if exists, with file
        Photo::query()
            ->where('entity_type', $request->entity_type)
            ->where('entity_id', $request->entity_id)
            ->first()?->delete();
        $photo = Photo::create($request->all());
        $photo->upload($request->file('photo'));

        return [
            'photo_url' => $photo->url,
        ];
    }

    /**
     * Загрузка прикреплённого фото в тексте (инструкции для преподавателя)
     */
    public function instructions(Request $request)
    {
        $name = Photo::uploadInstructionAttachment($request->file('photo'));

        return [
            'name' => $name,
        ];
    }
}
