<?php

namespace App\Excel\Exports\ERP;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class Ro1Export implements FromCollection, WithHeadings, WithEvents
{
    private $list;

    public function __construct($list)
    {
        $this->list = $list;
    }

    public function collection(): Collection
    {
        $arr = [];
        foreach ($this->list as $k => $v) {
            $arr[$k] = [
                '狀態' => $v['status'],
                '收貨日期' => $v['receivedDate'],
                '收料單編號' => $v['receiverNumber'],
                '項號' => $v['itemNumber'],
                '零件號' => $v['partNumber'],
                '種類' => $v['partType'],
                '描述' => $v['description'],
                '採購單號' => $v['poNumber'],
                '供應商編號' => $v['vendorId'],
                '採購單數量' => $v['qtyOrdered'],
                '採購單收貨數' => $v['poReceived'],
                '收貨數' => $v['qtyReceived'],
                '貨位' => $v['location'],
                '成本/單位' => $v['costPerUnit'],
                '稅率' => $v['taxRate'],
                '折扣率' => $v['discount'],
                '小計成本' => $v['extendedCost'],
                '貨幣' => $v['currency'],
                '發票' => $v['invoice'],
                '發票日期' => $v['invoiceDate'],
                '供應商銷售單號' => $v['vendorSalesOrder'],
                '應付/應付申請' => $v['payable'],
                '供應商發貨單' => $v['vendorPackage'],
                '採購收料日期' => $v['poShipDate'],
                '最初發貨日期' => $v['origialShipDate'],
                '供應商發貨日期' => $v['vendorShipDate'],
                '標準成本' => $v['standardCost'],
                '標準成本小計' => $v['standardExtendedCost'],
                '匯率' => $v['exchangeRate'],
                '會計科目' => $v['accountNumber'],
                '成本中心' => $v['companyUnitId'],
                '採購員' => $v['buyer'],
                '收貨人' => $v['receivedBy'],
                '條件' => $v['conditions'],
            ];
        }
        return new Collection($arr);
    }

    public function headings(): array
    {
        return [
            '狀態',
            '收貨日期',
            '收料單編號',
            '項號',
            '零件號',
            '種類',
            '描述',
            '採購單號',
            '供應商編號',
            '採購單數量',
            '採購單收貨數',
            '收貨數',
            '貨位',
            '成本/單位',
            '稅率',
            '折扣率',
            '小計成本',
            '貨幣',
            '發票',
            '發票日期',
            '供應商銷售單號',
            '應付/應付申請',
            '供應商發貨單',
            '採購收料日期',
            '最初發貨日期',
            '供應商發貨日期',
            '標準成本',
            '標準成本小計',
            '匯率',
            '會計科目',
            '成本中心',
            '採購員',
            '收貨人',
            '條件',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // todo set styles
            },
        ];
    }
}
