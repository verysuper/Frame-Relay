<?php

namespace App\Helper\ERP;

use Illuminate\Support\Facades\DB;

trait LogPoRcv {
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

    public function turn_po_log(): void
    {
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        try {
            $po_sql_str =
                "SELECT COUNT(*) as count " .
                "FROM information_schema.tables  " .
                "WHERE table_schema = DATABASE() " .
                "AND table_name = 'erp_log_po_inventory'; ";
            $checkTable = $pdo_erp->query($po_sql_str)->fetch(\PDO::FETCH_ASSOC);
            if (!$checkTable['count'] > 0) {
                $po_sql_str =
                    "CREATE TABLE erp_log_po_inventory " .
                    "LIKE erp_log_po_inventory_temp;";
                $pdo_erp->query($po_sql_str);
            }
            $po_sql_str =
                "SELECT " .
                "po,rn," .
                "DATE_FORMAT(LDATE,'%Y-%m-%d %H:%i') AS LDATE " .
                "FROM por " .
                "ORDER BY date_received DESC LIMIT 1";
            $por_last = $pdo_erp->query($po_sql_str)->fetchAll(\PDO::FETCH_CLASS)[0] ?? null;
            $po_sql_str =
                "SELECT " .
                "DATE_FORMAT(LDATE,'%Y-%m-%d %H:%i') AS LDATE " .
                "FROM erp_log_po_inventory " .
                "WHERE " .
                "order1 = '{$por_last->po}' and " .
                "order2 = '{$por_last->rn}' " .
                "ORDER BY ii DESC LIMIT 1;";
            $po_last = $pdo_erp->query($po_sql_str)->fetchAll(\PDO::FETCH_CLASS)[0] ?? null;
            if (!is_null($po_last) && $por_last->LDATE == $po_last->LDATE) {
                return;
            }
            $pdo_erp->query("truncate erp_log_po_inventory;");
            $po_sql_str =
                "SELECT " .
                "ii,pn,pa,LUSER,LDATE,in_qty,out_qty,ref_doc1,ref_doc2 " .
                "FROM " .
                "log_inventory ";
            $log_list = $pdo_erp->query($po_sql_str)->fetchAll(\PDO::FETCH_ASSOC) ?? [];
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
            $po_sql_str = "INSERT INTO erp_log_po_inventory VALUES " . implode(',', $log_sql_arr);
            $pdo_erp->query($po_sql_str);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
