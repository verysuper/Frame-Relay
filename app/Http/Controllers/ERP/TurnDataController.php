<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Helper\ERP\TurnData;

class TurnDataController extends Controller
{
    use TurnData;
    public function upPtCost()
    {
        $this->up_pt_cost();
    }
}
