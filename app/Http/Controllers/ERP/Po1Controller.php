<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use App\Excel\Exports\ERP\Po1Export;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Po1Controller extends Controller
{
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $input = \request()->all();
        $all_list = $this->queryToArray($input);
        return Excel::download(new Po1Export($all_list), '採購單.xlsx');
    }

    public function index()
    {
        return view('ERP.po_form1', [
            'list' => [],
            'start' => '2020-01-01',
            'end' => '',
            'status' => 'OPEN',
            'vendor' => '',
            'partNumber' => '',
        ]);
    }

    public function search()
    {
        $input = \request()->all();
        $all_list = $this->queryToArray($input);
        return view('ERP.po_form1', [
            'list' => $all_list,
            'start' => $input['start'],
            'end' => $input['end'],
            'status' => $input['status'],
            'vendor' => $input['vendor'],
            'partNumber' => $input['partNumber'],
        ]);
    }

    public function queryToArray($input)
    {
        $input = \request()->all();
        $pdo = DB::connection('2BizBox')->getPdo();
        $str =
            "SELECT ".
            "purchaseor1_.po AS 採購單號, ".
            "purchaseor1_.pi AS 項號, ".
            "purchaseor1_.delivery AS 發貨日期, ".
            "purchaseor1_.pn AS 刀具工具號, ".
            "purchaseor1_.pa AS 種類, ".
            "purchaseor1_.description AS 描述, ".
            "purchaseor1_.qty AS 採購單數量, ".
            "purchaseor1_.rcv AS 收貨數量, ".
            "purchaseor1_.qty - purchaseor1_.rcv AS 未交數量, ".
            "purchaseor1_.unit AS 單位, ".
            "purchaseor1_.cost AS 成本單位, ".
            "purchaseor1_.discount AS 折扣率, ".
            "purchaseor1_.ap_net AS 小計, ".
            "purchaseor1_.`status` AS 狀態, ".
            "purchaseor0_.buyer AS 採購員, ".
            "purchaseor0_.id AS 供應商, ".
            "purchaseor0_.entered AS 採購單日期, ".
            "purchaseor0_.project AS 項目, ".
            "purchaseor0_.requestor AS 申請人, ".
            "purchaseor1_.delay AS delayDays, ".
            "purchaseor1_.ap_open AS 未收料金額, ".
            "purchaseor1_.ap_rcvd AS 收料金額, ".
            "purchaseor0_.shipto AS 收貨方, ".
            "purchaseor0_.currency AS 貨幣, ".
            "purchaseor0_.exchange AS exchange, ".
            "purchaseor1_.taxr AS 稅率, ".
            "purchaseor1_.ap_tax AS 稅小計, ".
            "purchaseor1_.arrive_date AS 到達時間, ".
            "purchaseor0_.reldoc AS 相關文檔, ".
            "purchaseor1_.actual AS 最後收貨日期, ".
            "purchaseor0_.dacct1 AS 會計科目, ".
            "purchaseor0_.co_unit_id AS companyUnitId, ".
            "purchaseor1_.product_code AS 產品編碼, ".
            "purchaseor1_.sit AS 在途數, ".
            "purchaseor1_.original AS 原始發貨日期, ".
            "purchaseor1_.shipvia AS 發貨方式, ".
            "purchaseor0_.vendor_so AS 供應商銷售單號, ".
            "purchaseor1_.comments AS 工具備註 ".
            "FROM ".
            "po AS purchaseor0_ , ".
            "poi AS purchaseor1_ ".
            "WHERE ".
            "purchaseor0_.po = purchaseor1_.po AND ".
            "(purchaseor1_.po LIKE 'P%') AND ".
            ($input['status'] !== 'ALL' ? "(purchaseor1_.`status` LIKE '{$input['status']}%') AND ": '').
            ($input['vendor'] !== '' ? "(purchaseor0_.id LIKE '{$input['vendor']}%') AND ": '').
            ($input['partNumber'] !== '' ? "(purchaseor1_.pn LIKE '{$input['partNumber']}%') AND ": '').
            "(purchaseor0_.entered BETWEEN '{$input['start']}' AND '{$input['end']}') ".
            "ORDER BY ".
            "發貨日期 ASC, ".
            "採購單號 DESC, ".
            "項號 ASC ";
        $all_list = $pdo->query($str
            )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        return $all_list;
    }
}
