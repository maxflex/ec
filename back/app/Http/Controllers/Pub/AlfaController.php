<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Utils\AlfaPayment;
use Illuminate\Http\Request;

/**
 * Вебхуки от Альфа-Банка
 */
class AlfaController extends Controller
{
    public function __invoke(Request $request)
    {
        return AlfaPayment::handleWebhook($request);
    }
}
