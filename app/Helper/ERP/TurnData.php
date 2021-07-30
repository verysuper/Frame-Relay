<?php

namespace App\Helper\ERP;

use Illuminate\Support\Facades\DB;

trait TurnData {
    public function up_pt_cost(): void // init all pt last cost by poi.actual
    {
        $pdo_erp = DB::connection('2BizBox')->getPdo();
        $po_sql_str =
            "SELECT ".
            "poipn.* ".
            "FROM ".
            "( ".
            "SELECT ".
            "ps.pn,ps.pa ".
            "FROM ".
            "ps ".
            "WHERE ".
            "1 = 1 ".
            "AND ps.isdeleted = '' ".
            ") u1 ".
            "LEFT JOIN ( ".
            "SELECT ".
            "t.* ".
            "FROM ( ".
            "SELECT ".
            "poi.pn, ".
            "poi.pa, ".
            "ROUND(poi.cost / po.exchange, 4) as list_price, ".
            "ROUND(poi.cost / po.exchange, 4) as std_cost ".
            "FROM ".
            "po, ".
            "poi ".
            "WHERE ".
            "po.po = poi.po ".
            "AND poi.STATUS IN ('OPEN', 'CLOSED') ".
            "And poi.rcv > 0 ".
            "and poi.cost > 0 ".
            "ORDER BY poi.actual DESC ".
            ") t ".
            "GROUP BY t.pn, t.pa ".
            ") poipn ON u1.pn = poipn.pn AND u1.pa = poipn.pa ".
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
        $pdo_erp = null; // todo 防止更新正式機
        $pdo_erp_test = DB::connection('2BizBox_turn')->getPdo();
        $po_sql_str =
            "INSERT INTO pt (pn,pa,list_price,std_cost) VALUES " .
            implode(',', $pt_arr) .
            "ON DUPLICATE KEY UPDATE ".
            "pn=VALUES(pn), ".
            "pa=VALUES(pa), ".
            "list_price=VALUES(list_price), ".
            "std_cost=VALUES(std_cost);";
        $pts = $pdo_erp_test->query($po_sql_str)->fetchAll(\PDO::FETCH_ASSOC);
        dd($pts);
    }

}
