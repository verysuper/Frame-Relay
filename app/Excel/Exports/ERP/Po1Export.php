<?php

namespace App\Excel\Exports\ERP;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class Po1Export implements FromCollection, WithHeadings, WithEvents
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
                '供應商' => $v['供應商'],
                '採購單日期' => $v['採購單日期'],
                '採購單號' => $v['採購單號'],
                '項號' => $v['項號'],
                '刀具工具號' => $v['刀具工具號'],
                '種類' => $v['種類'],
                '描述' => $v['描述'],
                '原始發貨日期' => $v['原始發貨日期'],
                '發貨日期' => $v['發貨日期'],
                '採購單數量' => $v['採購單數量'],
                '收貨數量' => $v['收貨數量'],
                '未交數量' => $v['未交數量'],
                '工具備註' => $v['工具備註'],
                '成本單位' => $v['成本單位'],
                '貨幣' => $v['貨幣'],
                '匯率' => $v['exchange'],
                '稅率' => $v['稅率'],
                '單位' => $v['單位'],
                '採購員' => $v['採購員'],
                '折扣率' => $v['折扣率'],
                '小計' => $v['小計'],
                '狀態' => $v['狀態'],
                '項目' => $v['項目'],
                '申請人' => $v['申請人'],
                'delayDays' => $v['delayDays'],
                '未收料金額' => $v['未收料金額'],
                '收料金額' => $v['收料金額'],
                '收貨方' => $v['收貨方'],
                '稅小計' => $v['稅小計'],
                '到達時間' => $v['到達時間'],
                '相關文檔' => $v['相關文檔'],
                '最後收貨日期' => $v['最後收貨日期'],
                '會計科目' => $v['會計科目'],
                'companyUnitId' => $v['companyUnitId'],
                '產品編碼' => $v['產品編碼'],
                '在途數' => $v['在途數'],
                '發貨方式' => $v['發貨方式'],
                '供應商銷售單號' => $v['供應商銷售單號'],
            ];
        }
        return new Collection($arr);
    }

    public function headings(): array
    {
        return [
            '供應商',
            '採購單日期',
            '採購單號',
            '項號',
            '刀具工具號',
            '種類',
            '描述',
            '原始發貨日期',
            '發貨日期',
            '採購單數量',
            '收貨數量',
            '未交數量',
            '工具備註',
            '成本單位',
            '貨幣',
            '匯率',
            '稅率',
            '單位',
            '採購員',
            '折扣率',
            '小計',
            '狀態',
            '項目',
            '申請人',
            'delayDays',
            '未收料金額',
            '收料金額',
            '收貨方',
            '稅小計',
            '到達時間',
            '相關文檔',
            '最後收貨日期',
            '會計科目',
            'companyUnitId',
            '產品編碼',
            '在途數',
            '發貨方式',
            '供應商銷售單號',
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
