<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use App\Excel\Exports\ERP\Ro1Export;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Ro1Controller extends Controller
{
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $all_list = $this->queryToArray();
        return Excel::download(new Ro1Export($all_list), '收料單+發票.xlsx');
    }

    public function search()
    {
        $input = \request()->all();
        if (count($input) > 0) {
            $all_list = $this->queryToArray();
            return redirect()->route( 'erp.ro1' )->with([
                'list' => $all_list,
            ])->withInput();
        }
        return view('ERP.ro_form1');

    }

    public function queryToArray(): array
    {
        $input = \request()->all();
        $start = $input['start'];
        $end = $input['end'];
        $status = $input['status'];
        if ($status === 'RECEIVED') {
            $statusStr = "poir.`status` IN ('RECEIVED', 'PARTIAL') ";
        } elseif ($status === 'ALL') {
            $statusStr = "poir.`status` IN ('RECEIVED', 'PARTIAL', 'CLOSED') ";
        } else {
            $statusStr = "poir.`status` IN ('CLOSED') ";
        }
        $pdo = DB::connection('2BizBox')->getPdo();
        $_str =
            "SELECT ".
            "(CASE poir.`status` WHEN 'CLOSED' THEN '關閉' WHEN 'PARTIAL' THEN '未全部完成' ELSE '已收' END) AS `status`, ".
            "date(por.date_received) AS receivedDate, ".
            "poir.rn AS receiverNumber, ".
            "poir.pi AS itemNumber, ".
            "poir.pn AS partNumber, ".
            "poir.pa AS partType, ".
            "poi.description AS description, ".
            "poi.po AS poNumber, ".
            "por.id AS vendorId, ".
            "poi.qty AS qtyOrdered, ".
            "poi.rcv AS poReceived, ".
            "poir.qty_rcv AS qtyReceived, ".
            "poir.locate AS location, ".
            "poir.cost AS costPerUnit, ".
            "poir.taxr AS taxRate, ".
            "poir.discount AS discount, ".
            "poir.ap_net AS extendedCost, ".
            "por.currency AS currency, ".
            "por.vendor_invoice AS invoice,  ".
            "por.vendor_invoice_date AS invoiceDate,  ".
            "por.vendor_so AS vendorSalesOrder, ".
            "poir.ap AS payable, ".
            "por.vendor_package AS vendorPackage, ".
            "poi.original AS origialShipDate, ".
            "poi.delivery AS poShipDate, ".
            "por.vendor_ship_date AS vendorShipDate, ".
            "poir.std_cost AS standardCost, ".
            "(poir.std_cost * poir.qty_rcv) AS standardExtendedCost, ".
            "por.exchange AS exchangeRate, ".
            "por.dacct1 AS accountNumber, ".
            "por.co_unit_id AS companyUnitId, ".
            "por.buyor AS buyer, ".
            "poir.received_by AS receivedBy, ".
            "poir.check_condition AS conditions ".
            "FROM ".
            "poi , ".
            "por , ".
            "poir ".
            "LEFT JOIN ps ON poir.pn = ps.pn AND poir.pa = ps.pa ".
            "WHERE ".
            "poi.po = poir.po AND ".
            "por.rn = poir.rn AND ".
            "poi.pi = poir.poi AND ".
            "poir.pn_from = 'INVENTORY' AND ".
            ($input['vendor'] !== '' ? "(por.id LIKE '{$input['vendor']}%') AND ": '').
            ($input['partNumber'] !== '' ? "(poi.pn LIKE '{$input['partNumber']}%') AND ": '').
            "por.date_received BETWEEN '{$start} 00:00:00' AND '{$end} 23:59:59' AND ".
            $statusStr.
            "ORDER BY ".
            "receiverNumber DESC, ".
            "itemNumber ASC ";
        $all_list = $pdo->query($_str
            )->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        return $all_list;
    }
}
