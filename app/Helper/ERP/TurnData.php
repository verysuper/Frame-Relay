<?php

namespace App\Helper\ERP;

use Illuminate\Support\Facades\DB;

trait TurnData
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

    public function check_table($tableName, $type): void
    {
        $pdo_erp = DB::connection('2BizBox_turn')->getPdo();
        $sql_str =
            "SELECT COUNT(*) as count " .
            "FROM information_schema.tables  " .
            "WHERE table_schema = DATABASE() " .
            "AND table_name = '{$tableName}'; ";
        $checkTable = $pdo_erp->query($sql_str)->fetch(\PDO::FETCH_ASSOC);
        if (!$checkTable['count'] > 0) {
            $sql_str =
                "CREATE TABLE {$tableName} LIKE erp_log_{$type}_detail_temp;";
            $pdo_erp->query($sql_str);
        } else {
            $pdo_erp->query("truncate {$tableName};");
        }
    }

    public function turn_log_inventory(): void
    {
        $pdo_erp = DB::connection('2BizBox_turn')->getPdo();
        try {
            $sql_str =
                "SELECT COUNT(*) as count " .
                "FROM information_schema.tables  " .
                "WHERE table_schema = DATABASE() " .
                "AND table_name = 'erp_log_inventory'; ";
            $checkTable = $pdo_erp->query($sql_str)->fetch(\PDO::FETCH_ASSOC);
            if (!$checkTable['count'] > 0) {
                $sql_str =
                    "CREATE TABLE erp_log_inventory " .
                    "LIKE erp_log_inventory_temp;";
                $pdo_erp->query($sql_str);
            }
            $sql_str =
                "SELECT LDATE FROM log_inventory ORDER BY ii DESC LIMIT 1";
            $oldLog = $pdo_erp->query($sql_str)->fetchAll(\PDO::FETCH_CLASS)[0] ?? null;
            $sql_str =
                "SELECT LDATE FROM erp_log_inventory ORDER BY ii DESC LIMIT 1";
            $newLog = $pdo_erp->query($sql_str)->fetchAll(\PDO::FETCH_CLASS)[0] ?? null;
            if (!is_null($newLog) && $oldLog->LDATE == $newLog->LDATE) {
                return;
            }
            $pdo_erp->query("truncate erp_log_inventory;");
            $sql_str =
                "SELECT " .
                "ii,pn,pa,LUSER,LDATE,in_qty,out_qty,ref_doc1,ref_doc2 " .
                "FROM " .
                "log_inventory ";
            $log_list = $pdo_erp->query($sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
            $log_sql_arr = null;
            foreach ($log_list as $v) {
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
                if (in_array($type_str, ['P->R', 'W->W'])) {
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
            $sql_str = "INSERT INTO erp_log_inventory VALUES " . implode(',', $log_sql_arr);
            $pdo_erp->query($sql_str);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function up_pt_cost(): void // init all pt last cost by poi.actual // todo move to schedule
    {
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $po_sql_str =
            "SELECT " .
            "poipn.* " .
            "FROM " .
            "( " .
            "SELECT " .
            "ps.pn,ps.pa " .
            "FROM " .
            "ps " .
            "WHERE " .
            "1 = 1 " .
            "AND ps.isdeleted = '' " .
            ") u1 " .
            "LEFT JOIN ( " .
            "SELECT " .
            "t.* " .
            "FROM ( " .
            "SELECT " .
            "poi.pn, " .
            "poi.pa, " .
            "ROUND(poi.cost / po.exchange, 4) as list_price, " .
            "ROUND(poi.cost / po.exchange, 4) as std_cost " .
            "FROM " .
            "po, " .
            "poi " .
            "WHERE " .
            "po.po = poi.po " .
            "AND poi.STATUS IN ('OPEN', 'CLOSED') " .
            "And poi.rcv > 0 " .
            "and poi.cost > 0 " .
            "ORDER BY poi.actual DESC " .
            ") t " .
            "GROUP BY t.pn, t.pa " .
            ") poipn ON u1.pn = poipn.pn AND u1.pa = poipn.pa " .
            "WHERE poipn.pn <> '' ";
        $pts = $pdo_erp->query($po_sql_str)->fetchAll(\PDO::FETCH_ASSOC);
        if (!count($pts) > 0) {
            dd($pts);
        }
        $pt_arr = null;
        foreach ($pts as &$pt) {
            $pt_arr[] = str_replace(
                "''", 'NULL',
                "('" . implode("','", $pt) . "')"
            );
        }
        $pdo_erp = null;
        $pdo_erp_test = DB::connection('2BizBox_turn')->getPdo();
        $po_sql_str =
            "INSERT INTO pt (pn,pa,list_price,std_cost) VALUES " .
            implode(',', $pt_arr) .
            "ON DUPLICATE KEY UPDATE " .
            "pn=VALUES(pn), " .
            "pa=VALUES(pa), " .
            "list_price=VALUES(list_price), " .
            "std_cost=VALUES(std_cost);";
        $pts = $pdo_erp_test->query($po_sql_str)->fetchAll(\PDO::FETCH_ASSOC);
        dd($pts);
    }
}
