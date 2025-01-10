<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Загрузка файлов
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => ['file', 'max:10240'], // 10 MB
            'folder' => ['required', 'string']
        ]);
        sleep(10);
        $file = $request->file('file');
        $folder = $request->input('folder');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('crm/' . $folder, $fileName);
        return cdn($folder, $fileName);
    }
}
