<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function CarbonSimDate()
    {
        $offsetDays = session('sim_date_offset', 0);
        $startDate = \Carbon\Carbon::create(2026, 6, 11, 13, 40, 0);
        return $startDate->addDays($offsetDays);
    }
}
