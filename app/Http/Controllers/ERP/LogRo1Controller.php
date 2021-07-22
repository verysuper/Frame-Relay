<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LogRo1Controller extends Controller
{
    public function index()
    {
        return view('ERP.logRo_form1', [
            'ro_total' => [],
            'ro_off_list' => [],
            'start' => date('Y-m-d'),
            'end' => date('Y-m-d'),
        ]);
    }

    public function search()
    {
        $input = \request()->all();
        $ro_total = $this->ro_total();
        return view('ERP.logRo_form1', [
            'ro_total' => $ro_total,
//            'ro_off_list' => $ro_off_list,
            'start' => $input['start'],
            'end' => $input['end'],
        ]);
    }

    public function ro_total(): array
    {
        $input = \request()->all();
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $ro_get_str =
            "SELECT abc.vname, ro.* FROM ( " .
            "SELECT " .
            "por.id AS vendorId, " .
            "CONCAT(por.rn,'.',poi.pi) as flag, " .
            "poi.`status` AS `STATUS`, " .
            "poi.pn AS partNumber, " .
            "poi.pa AS partType, " .
            "poi.rcv AS qtyReceived, " .
            "DATE_FORMAT(por.date_received,'%Y-%m') AS receivedMonth, " .
            "por.date_received AS receivedDate, " .
            "poi.qty AS qtyOrdered, " .
            "poi.rcv AS poReceived, " .
            "por.rn AS receiverNumber, " .
            "poi.pi AS itemNumber, " .
            "poi.po AS poNumber, " .
            "poi.description AS description, " .
            "'poir有bug' AS location, " .
            "por.buyor AS buyer, " .
            "poi.ap_net AS extendedCost, " .
            "poi.cost AS costPerUnit, " .
            "poi.taxr AS taxRate, " .
            "por.currency AS currency, " .
            "por.exchange AS exchangeRate, " .
            "pt.std_cost AS standardCost, " .
            "por.received_by AS receivedBy, " .
            "( pt.std_cost * poi.rcv ) AS standardExtendedCost, " .
            "por.vendor_invoice AS invoice,  ".
            "por.vendor_invoice_date AS invoiceDate,  ".
            "'poir有bug' AS conditions " .
            "FROM " .
            "por " .
            "LEFT JOIN poi ON por.po = poi.po " .
            "LEFT JOIN pt ON pt.pn = poi.pn AND pt.pa = poi.pa " .
            "WHERE " .
            "poi.pn_from = 'INVENTORY' AND " .
            "por.date_received BETWEEN '{$input['start']} 00:00:00' AND '{$input['end']} 23:59:59' " .
            "ORDER BY por.rn DESC, poi.pi ASC " .
            ") ro LEFT JOIN ( " .
            "SELECT ab.id AS vid, ab.NAME AS vname " .
            "FROM ab LEFT OUTER JOIN abaa ON ab.id = abaa.add_id  " .
            "WHERE 1 = 1  " .
            "AND ab.isSalesAddr = '0'  " .
            "AND ( ab.addr_type = 'V' OR ab.addr_type = 'A' OR ab.addr_type = '' )  " .
            "AND ab.isdeleted = 0 " .
            "GROUP BY ab.id, ab.name " .
            ") abc ON abc.vid = ro.vendorId ";
        $ro_list = $pdo_erp->query($ro_get_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        $ro_list_arr = null;
        $ro_total_arr = null;
        foreach ($ro_list as $ro) {
            $ro_list_arr[]= str_replace(
                "''",'NULL',
                "('".implode("','",$ro)."')"
            );
            $ro['ap_total'] = $ro['poReceived'] * $ro['costPerUnit'] * (1 + $ro['taxRate'] * 0.01);
            $ro_total_arr[$ro['vname']][$ro['receivedMonth']][$ro['taxRate'] > 0 ? 'Y' : 'N'][] = $ro;
        }
        $maxMonth = 0;
        foreach ($ro_total_arr as $vendorK => $vendorV) {
            $maxMonth = count($vendorV) > $maxMonth ? count($vendorV) : $maxMonth;
            $vendor_y = 0;
            $vendor_n = 0;
            foreach ($vendorV as $monthK => $monthV) {
                $month_y = 0;
                $month_n = 0;
                if (isset($monthV['Y'])) {
                    foreach ($monthV['Y'] as $ro) {
                        $vendor_y += $ro['ap_total'];
                        $month_y += $ro['ap_total'];
                    }
                }
                if (isset($monthV['N'])) {
                    foreach ($monthV['N'] as $ro) {
                        $vendor_n += $ro['ap_total'];
                        $month_n += $ro['ap_total'];
                    }
                }
                $ro_total_arr[$vendorK][$monthK]['month_total_y'] = $month_y;
                $ro_total_arr[$vendorK][$monthK]['month_total_n'] = $month_n;
            }
            $ro_total_arr[$vendorK]['vendor_total_y'] = $vendor_y;
            $ro_total_arr[$vendorK]['vendor_total_n'] = $vendor_n;
            $ro_total_arr['maxMonth'] = $maxMonth;
        }

        $ro_list_str = "INSERT INTO log_ro VALUES ".implode(',',$ro_list_arr);
        $pdo_local = DB::connection('mysql')->getPdo();
        $pdo_local->query("truncate log_ro;");
        $pdo_local->query($ro_list_str);
        $ro_off_list = $this->check_ro($ro_list);
        $ro_off_arr = null;
        foreach ($ro_off_list as $ro) {
            $ro_off_arr[]= str_replace(
                "''",'NULL',
                "('".implode("','",$ro)."')"
            );
        }
        $ro_off_str = "INSERT INTO log_ro_off VALUES ".implode(',',$ro_off_arr);
        $pdo_local->query("truncate log_ro_off;");
        $pdo_local->query($ro_off_str);
        return $ro_total_arr;
    }

    public function check_ro($ro): array
    {
        $input = \request()->all();
        $types = [
            'RESET COST',
            'WAREHOUSE',
            'WareHouse',
            'INVENTORY',
            'QUALITY',
            'LNSH',
            'LNR',
            'NCR',
            'TSH',
            'SWS',
            'SWR',
            'LN',
            'DW',
            'LN',
            'SH',
            'D',
            'N',
            'P',
            'R',
            'S',
            'T',
            'W',
        ];
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $log_Str =
            "SELECT " .
            "log.pn , " .
            "log.pa , " .
            "log.LUSER, " .
            "log.LDATE, " .
            "log.in_qty , " .
            "log.out_qty , " .
            "log.ref_doc1 , " .
            "log.ref_doc2  " .
            "FROM " .
            "log_inventory log " .
            "WHERE " .
            "log.LDATE BETWEEN '{$input['start']} 00:00:00' AND '{$input['end']} 23:59:59' " .
            "ORDER BY " .
            "log.ii DESC  ";
        $log_list = $pdo_erp->query($log_Str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        $log_wh = (object)[];
        foreach ($log_list as $k => $v) {
            $type_str = "";
            foreach ($types as $type) {
                if (strpos($v["ref_doc1"], $type) === 0) {
                    $type_str .= $type;
                    break;
                }
            }
            foreach ($types as $type) {
                if (strpos($v["ref_doc2"], $type) === 0) {
                    $type_str .= '->' . $type;
                    break;
                }
            }
            if ($type_str == 'W') {
                $type_str .= '->數字';
            }
            if ($type_str == '') {
                $type_str .= '???';
            }
            if ($type_str == 'P->R') {
                $log_wh->{$v['ref_doc2']} = $v;
            }
        }
        $ro_off_list = null;
        // 檢查有無倉庫紀錄
        $log_ro = (object)[];
        foreach ($ro as $k => $v) {
            if (!property_exists($log_wh, $v['flag'])) {
                $ro_off_list[] = [
                    'pn' => $v['partNumber'],
                    'pa' => $v['partType'],
                    'LUSER' => $v['receivedBy'],
                    'LDATE' => $v['receivedDate'],
                    'ref_doc1' => $v['poNumber'].'.'.$v['itemNumber'],
                    'ref_doc2' => $v['flag'],
                    'log_on' => 'N',
                    'order_on' => null,
                ];
            }
            $log_ro->{$v['flag']} = $v;
        }
        // 檢查有無收料紀錄
        foreach ($log_wh as $k => $v) {

            if (!property_exists($log_ro, $v['ref_doc2'])) {
                $ro_off_list[] = [
                    'pn' => $v['pn'],
                    'pa' => $v['pa'],
                    'LUSER' => $v['LUSER'],
                    'LDATE' => $v['LDATE'],
                    'ref_doc1' => $v['ref_doc1'],
                    'ref_doc2' => $v['ref_doc2'],
                    'log_on' => null,
                    'order_on' => 'N',
                ];
            }
        }
        return $ro_off_list;
    }
}
