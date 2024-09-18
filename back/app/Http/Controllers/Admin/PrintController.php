<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractVersion;
use App\Models\Macro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;


class PrintController extends Controller
{
    public function show($id, Request $request)
    {
        $macro = Macro::findOrFail($id);

        // Render the template with Blade and pass variables
        $renderedText = Blade::render($macro->text, [
            'contractVersion' => ContractVersion::find($request->contract_version_id)
        ]);

        return [
            'text' => $renderedText
        ];
    }
}
