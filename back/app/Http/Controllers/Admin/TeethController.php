<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\HasTeeth;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class TeethController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'entity_type' => ['required', 'string'],
            'entity_id' => ['required', 'min:0'],
            'year' => ['required', 'min:0'],
        ]);
        $entityClass = $request->entity_type;
        $entity = $entityClass::findOrFail($request->entity_id);
        if (!$entity instanceof HasTeeth) {
            throw new Exception("$entityClass does not implement HasTeeth");
        }
        return $entity->getTeeth(intval($request->year));
    }
}
