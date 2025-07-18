<?php

namespace App\Http\Controllers;

use App\Services\GeneralService;

abstract class Controller
{
    protected GeneralService $generalService;

    public function __construct(GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }
}
