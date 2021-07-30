<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Excel\Exports\ERP\LogPoRcvExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Helper\Tools\Common;
use App\Helper\ERP\LogPoRcv;

class LogPoRcvController extends Controller
{
    use Common, LogPoRcv;
    /**
     * @throws Exception
     */
    public function search(Request $request)
    {
        $input = $request->except('XDEBUG_SESSION_START');
        if (!count($input) > 0) {
            return view('ERP.logPoRcv');
        }
        $this->turn_po_log();
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $ipArr = explode(".", $this->getIp());
        $tableName = "erp_log_po_detail_{$ipArr[2]}_{$ipArr[3]}";
        $po_sql_str =
            "SELECT COUNT(*) as count " .
            "FROM information_schema.tables  " .
            "WHERE table_schema = DATABASE() " .
            "AND table_name = '{$tableName}'; ";
        $checkTable = $pdo_erp->query($po_sql_str)->fetch(\PDO::FETCH_ASSOC);
        if (!$checkTable['count'] > 0) {
            $po_sql_str =
                "CREATE TABLE {$tableName} LIKE erp_log_po_detail_temp;";
            $pdo_erp->query($po_sql_str);
        } else {
            $pdo_erp->query("truncate {$tableName};");
        }
        $po_sql_str =
            "SELECT " .
            "erp_log_po_inventory.pn, " .
            "erp_log_po_inventory.pa, " .
            "erp_log_po_inventory.in_qty, " .
            "erp_log_po_inventory.out_qty, " .
            "erp_log_po_inventory.ref_doc1, " .
            "erp_log_po_inventory.ref_doc2, " .
            "poi.cost AS costPerUnit, " .
            "por.currency, " .
            "por.exchange AS exchangeRate, " .
            "por.id AS vendorId, " .
            "ab.`name` AS vendorName, " .
            "ab.vtaxr AS taxRate, " .
            "por.`status` AS porStatus, " .
            "por.vendor_invoice AS invoice, " .
            "por.vendor_invoice_date AS invoiceDate, " .
            "poi.`status` AS poiStatus, " .
            "poi.qty AS demandQty, " .
            "poi.rcv AS receivedQty, " .
            "erp_log_po_inventory.LDATE AS rcvDate, " .
            "erp_log_po_inventory.LUSER AS user, " .
            "DATE_FORMAT(erp_log_po_inventory.LDATE,'%Y-%m') AS rcvMonth, " .
            "poi.description AS description " .
            "FROM " .
            "erp_log_po_inventory " .
            "LEFT JOIN poi ON poi.po = erp_log_po_inventory.order1 AND poi.pi = erp_log_po_inventory.item1 " .
            "LEFT JOIN por ON por.po = erp_log_po_inventory.order1 AND por.rn = erp_log_po_inventory.order2 " .
            "LEFT JOIN ab ON ab.id = por.id " .
            "WHERE " .
            "erp_log_po_inventory.LDATE BETWEEN '{$input['start']} 00:00:00' AND '{$input['end']} 23:59:59' " .
            "ORDER BY erp_log_po_inventory.LDATE DESC ";
        $po_list = $pdo_erp->query($po_sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        if (!count($po_list) > 0) {
            return redirect()->route('erp.logPoRcv')->with([
                'po_total' => $po_list,
            ])->withInput();
        }
        $log_sql_arr = null;
        $po_total_arr = null;
        foreach ($po_list as &$po) {
            $po['ap_net'] =
                ($po['in_qty'] - $po['out_qty']) * // 數量
                $po['costPerUnit'] * // 單價
                (1 / $po['exchangeRate']); //匯率
            $po['ap_tax'] = $po['ap_net'] * ($po['taxRate'] * 0.01); // 5%
            $log_sql_arr[] = str_replace(
                "''", 'NULL',
                "('" . implode("','", $po) . "')"
            );
            $po_total_arr[$po['vendorName']][$po['rcvMonth']][] = $po;
        }
        $po_sql_str =
            "INSERT INTO {$tableName} VALUES " .
            implode(',', $log_sql_arr);
        $pdo_erp->query($po_sql_str);
        $po_total_obj = (object)[];
        foreach ($po_total_arr as $vendorK => $vendorV) {
            $vendor = (object)[];
            $vendor->net = 0;
            $vendor->tax = 0;
            foreach ($vendorV as $monthK => $monthV) {
                $month = (object)[];
                $month->net = 0;
                $month->tax = 0;
                foreach ($monthV as $v) {
                    $vendor->net += $v['ap_net'];
                    $vendor->tax += $v['ap_tax'];
                    $month->net += $v['ap_net'];
                    $month->tax += $v['ap_tax'];
                }
                $vendor->{$monthK} = $month;
            }
            $po_total_obj->{$vendorK} = $vendor;
        }
        $start = (new \DateTime($input['start']))->modify('first day of this month');
        $end = (new \DateTime($input['end']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $columns = null;
        foreach ($period as $dt) {
            $columns[] = $dt->format("Y-m");
        }
        return redirect()->route('erp.logPoRcv')->with([
            'po_total' => $po_total_obj,
            'columns' => $columns,
        ])->withInput();
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $ipArr = explode(".", $this->getIp());
        $tableName = "erp_log_po_detail_{$ipArr[2]}_{$ipArr[3]}";
        $po_sql_str =
            "SELECT * FROM {$tableName}";
        $all_list = $pdo_erp->query($po_sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        return Excel::download(new LogPoRcvExport($all_list), '採購單→收料單(NTD).xlsx');
    }
}
