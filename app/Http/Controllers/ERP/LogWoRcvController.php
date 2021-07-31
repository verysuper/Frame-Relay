<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
use Exception;
use Illuminate\Support\Facades\DB;
//use App\Excel\Exports\ERP\LogWoRcvExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Helper\Tools\Common;
use App\Helper\ERP\TurnData;

class LogWoRcvController extends Controller
{
    use Common, TurnData;
    /**
     * @throws Exception
     */
    public function search(Request $request)
    {
        $input = $request->except('XDEBUG_SESSION_START');
        if (!count($input) > 0) {
            return view('ERP.logWoRcv');
        }
        $this->turn_log_inventory();
        $ipArr = explode(".", $this->getIp());
        $tableName = "erp_log_wo_detail_{$ipArr[2]}_{$ipArr[3]}";
        $this->check_table($tableName, 'wo');
        // todo 待討論工單抓哪一種入庫型態產生應付帳款
        return redirect()->route('erp.logWoRcv')->with([
            'wo_total' => [],
            'columns' => '',
        ])->withInput();
    }
}
