<?php

namespace App\Utils;

use Illuminate\Http\Request;

class Stats
{
    public function __construct(public Request $request)
    {
    }

    public function getData()
    {
        return $this->request->all();
    }
}
