<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Utils\AvailableYears;
use Illuminate\Http\Request;

/**
 * Получить все возможные годы для срезки
 */
class AvailableYearsController extends Controller
{
    public function __invoke(Request $request)
    {
        return paginate(
            AvailableYears::get($request)
        );
    }
}
