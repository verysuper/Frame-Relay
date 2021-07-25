<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Excel\Exports\ERP\LogTotalRoExport;
use Maatwebsite\Excel\Facades\Excel;

class LogTotalRoController extends Controller
{
    public $log_types = [
        'RESET COST',
        'WAREHOUSE',
        'WareHouse',
        'INVENTORY',
        'QUALITY',
        'RTVSH',
        'LNSH',
        'RMAR',
        'LNR',
        'NCR',
        'TSH',
        'RMA',
        'RTV',
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

    public function index()
    {
        return view('ERP.logTotalRo', [
            'ro_total' => [],
            'ro_off_list' => [],
            'start' => date('Y-m-d'),
            'end' => date('Y-m-d'),
            'interval' => '',
        ]);
    }

    public function turn_yesterday_log(): void
    {
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        try {
            // todo copy temp table
            $ro_sql_str =
                "CREATE TABLE IF NOT EXISTS `erp_log_ro_inventory` ( " .
                "`ii` bigint(191) NOT NULL, " .
                "`pn` varchar(191) default NULL, " .
                "`pa` varchar(191) default NULL, " .
                "`LUSER` varchar(191) default NULL, " .
                "`LDATE` datetime default NULL, " .
                "`in_qty` float(191,4) default NULL, " .
                "`out_qty` float(191,4) default NULL, " .
                "`ref_doc1` varchar(191) default NULL, " .
                "`ref_doc2` varchar(191) default NULL, " .
                "`type1` varchar(191) default NULL, " .
                "`type2` varchar(191) default NULL, " .
                "`type0` varchar(191) default NULL, " .
                "`order1` varchar(191) default NULL, " .
                "`order2` varchar(191) default NULL, " .
                "`item1` varchar(191) default NULL, " .
                "`item2` varchar(191) default NULL " .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
            $pdo_erp->query($ro_sql_str);
            $yesterday = date('Y-m-d', strtotime("-1 days"));
            $ro_sql_str =
                "SELECT DATE_FORMAT(LDATE,'%Y-%m-%d') AS yesterday " .
                "FROM `erp_log_ro_inventory` " .
                "ORDER BY LDATE DESC LIMIT 1 ";
            $last_date = $pdo_erp->query($ro_sql_str)->fetchAll(\PDO::FETCH_CLASS)[0] ?? null;
            if (isset($last_date) && $last_date->yesterday === $yesterday) {
                return;
            }
            $pdo_erp->query("truncate erp_log_ro_inventory;");
            $ro_sql_str =
                "SELECT " .
                "ii,pn,pa,LUSER,LDATE,in_qty,out_qty,ref_doc1,ref_doc2 " .
                "FROM " .
                "log_inventory " .
                "WHERE LDATE BETWEEN '1970-01-01 00:00:00' AND '{$yesterday} 23:59:59'";
            $log_list = $pdo_erp->query($ro_sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
            $log_sql_arr = null;
            foreach ($log_list as $k => $v) {
                $type_str = "";
                foreach ($this->log_types as $type) {
                    if (strpos($v["ref_doc1"], $type) === 0) {
                        $v['type1'] = $type;
                        $type_str .= $type;
                        break;
                    }
                }
                foreach ($this->log_types as $type) {
                    if (strpos($v["ref_doc2"], $type) === 0) {
                        $v['type2'] = $type;
                        $type_str .= '->' . $type;
                        break;
                    }
                }
                if ($type_str == 'W') {
                    $v['type2'] = '數字';
                    $type_str .= '->數字';
                }
                if ($type_str == '') {
                    $v['type1'] = '???';
                    $v['type2'] = '???';
                    $type_str .= '???';
                }
                if (in_array($type_str, ['P->R'])) {
                    $v['type0'] = $type_str;
                    $v['order1'] = explode('.', $v['ref_doc1'])[0] ?? $v['ref_doc1'];
                    $v['order2'] = explode('.', $v['ref_doc2'])[0] ?? $v['ref_doc2'];
                    $v['item1'] = explode('.', $v['ref_doc1'])[1] ?? $v['ref_doc1'];
                    $v['item2'] = explode('.', $v['ref_doc2'])[1] ?? $v['ref_doc2'];
                    $log_sql_arr[] = str_replace(
                        "''", 'NULL',
                        "('" . implode("','", $v) . "')"
                    );
                }
            }
            $ro_sql_str = "INSERT INTO erp_log_ro_inventory VALUES " . implode(',', $log_sql_arr);
            $pdo_erp->query($ro_sql_str);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function getIp(): ?string
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }

    /**
     * @throws Exception
     */
    public function search()
    {
        $input = \request()->all();
        $this->turn_yesterday_log();
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $ipArr = explode(".", $this->getIp());
        $tableName = "erp_log_ro_detail_{$ipArr[2]}_{$ipArr[3]}";
        $ro_sql_str =
            "SELECT COUNT(*) as count " .
            "FROM information_schema.tables  " .
            "WHERE table_schema = DATABASE() " .
            "AND table_name = '{$tableName}'; ";
        $checkTable = $pdo_erp->query($ro_sql_str)->fetch(\PDO::FETCH_ASSOC);
        if (!$checkTable['count'] > 0) {
            $ro_sql_str =
                "CREATE TABLE {$tableName} LIKE erp_log_ro_detail_temp;";
            $pdo_erp->query($ro_sql_str);
        } else {
            $pdo_erp->query("truncate {$tableName};");
        }
        $ro_sql_str =
            "SELECT " .
            "erp_log_ro_inventory.pn, " .
            "erp_log_ro_inventory.pa, " .
            "erp_log_ro_inventory.in_qty, " .
            "erp_log_ro_inventory.out_qty, " .
            "erp_log_ro_inventory.ref_doc1, " .
            "erp_log_ro_inventory.ref_doc2, " .
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
            "erp_log_ro_inventory.LDATE AS rcvDate, " .
            "erp_log_ro_inventory.LUSER AS user, " .
            "DATE_FORMAT(erp_log_ro_inventory.LDATE,'%Y-%m') AS rcvMonth, " .
            "poi.description AS description " .
            "FROM " .
            "erp_log_ro_inventory " .
            "LEFT JOIN poi ON poi.po = erp_log_ro_inventory.order1 AND poi.pi = erp_log_ro_inventory.item1 " .
            "LEFT JOIN por ON por.po = erp_log_ro_inventory.order1 AND por.rn = erp_log_ro_inventory.order2 " .
            "LEFT JOIN ab ON ab.id = por.id " .
            "WHERE " .
            "erp_log_ro_inventory.LDATE BETWEEN '{$input['start']} 00:00:00' AND '{$input['end']} 23:59:59' " .
            "ORDER BY erp_log_ro_inventory.LDATE DESC ";
        $ro_list = $pdo_erp->query($ro_sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        if (!count($ro_list) > 0) {
            return view('ERP.logTotalRo', [
                'ro_total' => $ro_list,
                'start' => $input['start'],
                'end' => $input['end'],
            ]);
        }
        $log_sql_arr = null;
        $ro_total_arr = null;
        foreach ($ro_list as &$ro) {
            $ro['ap_net'] =
                ($ro['in_qty'] - $ro['out_qty']) * // 數量
                $ro['costPerUnit'] * // 單價
                (1 / $ro['exchangeRate']); //匯率
            $ro['ap_tax'] = $ro['ap_net'] * ($ro['taxRate'] * 0.01); // 5%
            $log_sql_arr[] = str_replace(
                "''", 'NULL',
                "('" . implode("','", $ro) . "')"
            );
            $ro_total_arr[$ro['vendorName']][$ro['rcvMonth']][] = $ro;
        }
        $ro_sql_str =
            "INSERT INTO {$tableName} VALUES " .
            implode(',', $log_sql_arr);
        $pdo_erp->query($ro_sql_str);
        $ro_total_obj = (object)[];
        foreach ($ro_total_arr as $vendorK => $vendorV) {
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
            $ro_total_obj->{$vendorK} = $vendor;
        }
        $start = (new \DateTime($input['start']))->modify('first day of this month');
        $end = (new \DateTime($input['end']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $columns = null;
        foreach ($period as $dt) {
            $columns[] = $dt->format("Y-m");
        }
        return view('ERP.logTotalRo', [
            'ro_total' => $ro_total_obj,
            'start' => $input['start'],
            'end' => $input['end'],
            'columns' => $columns,
        ]);
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $ipArr = explode(".", $this->getIp());
        $tableName = "erp_log_ro_detail_{$ipArr[2]}_{$ipArr[3]}";
        $ro_sql_str =
            "SELECT * FROM {$tableName}";
        $all_list = $pdo_erp->query($ro_sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        return Excel::download(new LogTotalRoExport($all_list), '採購單→收料單(統計NTD).xlsx');
    }
}
