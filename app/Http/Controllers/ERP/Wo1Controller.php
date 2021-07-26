<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use App\Excel\Exports\ERP\Wo1Export;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Wo1Controller extends Controller
{
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $all_list = $this->queryToArray();
        return Excel::download(new Wo1Export($all_list), '工單調度.xlsx');
    }

    public function search()
    {
        $input = \request()->all();
        if (count($input) > 0) {
            $all_list = $this->queryToArray();
            return redirect()->route( 'erp.wo1' )->with([
                'list' => $all_list,
            ])->withInput();
        }
        return view('ERP.wo_form1');
    }

    public function queryToArray(): array
    {
        $input = \request()->all();
        $start = $input['start'] ?? '';
        $end = $input['end'] ?? '';
        $pdo = DB::connection('2BizBox')->getPdo();
        $all_list = [];
        $po_str =
            "SELECT " .
            "'' AS orderNumber, " .
            "'' AS orderItemNumber, " .
            "poi.pn AS partNumber, " .
            "poi.pa AS partType, " .
            "poi.description AS partDescription, " .
            "poi.qty AS quantity, " .
            "'' AS issuedQuatity, " .
            "poi.rcv AS quantityReceived, " .
            "pt.onhand AS quantityOnhand, " .
            "poi.qty - poi.rcv AS 在途數, " .
            "'' AS 調整數, " .
            "'' AS 短缺預測, " .
            "po.id AS vendorID, " .
            "ab.`name` AS vendorName, " .
            "poi.delivery AS dueDate, " .
            "poi.delivery AS startDate, " .
            "ps.buyer AS buyerCode, " .
            "poi.`status` AS `status`, " .
            "ps.make_buy AS makeBuy, " .
            "pt.sit AS inSit, " .
            "'' AS preferredPo, " .
            "poi.po AS 採購單, " .
            "poi.pi AS 採購單項, " .
            "'0' AS lv " .
            "FROM " .
            "poi " .
            "LEFT JOIN po ON poi.po = po.po " .
            "LEFT JOIN ab ON po.id = ab.id " .
            "LEFT JOIN ps ON poi.pn = ps.pn AND poi.pa = ps.pa " .
            "LEFT JOIN pt ON poi.pn = pt.pn AND poi.pa = pt.pa " .
            "WHERE " .
            "poi.`status` = 'open' AND " .
            "poi.delivery >= '{$start}' AND poi.delivery <= '{$end}' ";
        $po_list = $pdo->query($po_str
            )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        array_push($all_list, ...$po_list);
        $wo_str =
            "SELECT " .
            "DISTINCT woi.wo AS orderNumber, " .
            "woi.wi AS orderItemNumber, " .
            "woi.pn AS partNumber, " .
            "woi.pa AS partType, " .
            "woi.description AS partDescription, " .
            "woi.qty AS quantity, " .
            "'' AS issuedQuatity, " .
            "woi.asm AS quantityReceived, " .
            "pt.onhand AS quantityOnhand, " .
            "'' AS 在途數, " .
            "'' AS 調整數, " .
            "'' AS 短缺預測, " .
            "wo.id AS vendorID, " .
            "ab.`name` AS vendorName, " .
            "woi.delivery AS dueDate, " .
            "woi.start_date AS startDate, " .
            "ps.buyer AS buyerCode, " .
            "woi.`status` AS `status`, " .
            "ps.make_buy AS makeBuy, " .
            "pt.sit AS inSit, " .
            "pv.id AS preferredPo, " .
            "'' AS 採購單, " .
            "'' AS 採購單項, " .
            "'1' AS lv " .
            "FROM " .
            "woi " .
            "LEFT JOIN wo ON woi.wo = wo.wo " .
            "LEFT JOIN ab ON wo.id = ab.id " .
            "LEFT JOIN ps ON woi.pn = ps.pn AND woi.pa = ps.pa " .
            "LEFT JOIN pt ON woi.pn = pt.pn AND woi.pa = pt.pa " .
            "LEFT JOIN refdoc ON woi.wo = refdoc.forder AND woi.wi = refdoc.fitem " .
            "LEFT JOIN pv ON woi.pn = pv.pn AND woi.pa = pv.pa AND pv.preferred = 1 " .
            "WHERE " .
            "woi.`status` = 'open' AND " .
            "woi.delivery >= '{$start}' AND woi.delivery <= '{$end}' ";
        $wo_list = $pdo->query($wo_str
            )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        foreach ($wo_list as $key => $item) {
            array_push($all_list, $item);
            $woi_str = "SELECT " .
                "wois.wo AS orderNumber, " .
                "wois.wi AS orderItemNumber, " .
                "wois.pn AS partNumber, " .
                "wois.pa AS partType, " .
                "wois.description AS partDescription, " .
                "wois.qty AS quantity, " .
                "wois.isu AS issuedQuatity, " .
                "'' AS quantityReceived, " .
                "pt.onhand AS quantityOnhand, " .
                "'' AS 在途數, " .
                "'' AS 調整數, " .
                "'' AS 短缺預測, " .
                "pv.id AS vendorID, " .
                "pv.name AS vendorName, " .
                "'{$item['dueDate']}' AS dueDate, " .
                "'{$item['startDate']}' AS startDate, " .
                "ps.buyer AS buyerCode, " .
                "'' AS `status`, " .
                "ps.make_buy AS makeBuy, " .
                "pt.sit AS inSit, " .
                "pv.id AS preferredPo, " .
                "'' AS 採購單, " .
                "'' AS 採購單項, " .
                "'2' AS lv " .
                "FROM " .
                "wois " .
                "LEFT JOIN ps ON wois.pn = ps.pn AND wois.pa = ps.pa " .
                "LEFT JOIN pt ON wois.pn = pt.pn AND wois.pa = pt.pa " .
                "LEFT JOIN pv ON wois.pn = pv.pn AND wois.pa = pv.pa AND pv.preferred = 1 " .
                "WHERE " .
                "wois.wo = '{$item['orderNumber']}' AND " .
                "wois.wi = '{$item['orderItemNumber']}' ";
            $woi_list = $pdo->query($woi_str
                )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
            array_push($all_list, ...$woi_list);
        }
        if (count($all_list) < 1) {
            return [];
        }
        foreach ($all_list as $key => $row) {
            $sortDate[$key] = $row['dueDate'];
            $orderNumber[$key] = $row['orderNumber'];
            $lv[$key] = $row['lv'];
        }
        array_multisort($sortDate, SORT_ASC, $orderNumber, SORT_ASC, $lv, SORT_ASC, $all_list);
        $pt_list2 = (object)[];
        foreach ($all_list as $key => $value) {
            if ($value['partNumber'] === '41-SD015-10-0') {
                echo '';
            }
            if ($value['lv'] == 2) {
                $revision =
                    floatval($value['quantityReceived']) -
                    (floatval($value['quantity']) - floatval($value['issuedQuatity']));
            } else {
                $revision =
                    (floatval($value['quantity']) - floatval($value['quantityReceived']));
            }
            if (isset($pt_list2->{$value['partNumber']})) {
                $predict = floatval($pt_list2->{$value['partNumber']}->{'短缺預測'});
                $all_list[$key]['調整數'] = floatval($pt_list2->{$value['partNumber']}->{'短缺預測'});
                $all_list[$key]['短缺預測'] = $predict + $revision;
            } else {
                $all_list[$key]['短缺預測'] = floatval($value['quantityOnhand']) + $revision;
            }
            if ($value['partNumber'] != 'NA') {
                $pt_item = (object)[
                    '短缺預測' => $all_list[$key]['短缺預測'],
                    '調整數' => $revision,
                ];
                $pt_list2->{$value['partNumber']} = $pt_item;
            }
        }
        return $all_list;
    }
}
