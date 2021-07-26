<?php

namespace App\Excel\Exports\ERP;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class Wo1Export implements FromCollection, WithHeadings, WithEvents
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
                '工單' => $v['orderNumber'],
                '工單項' => $v['orderItemNumber'],
                'pNum' => $v['partNumber'],
                'pType' => $v['partType'],
                'pDes' => $v['partDescription'],
                '數量' => $v['quantity'],
                '已發' => $v['issuedQuatity'],
                '已收' => $v['quantityReceived'],
                '庫存' => $v['quantityOnhand'],
                '在途數' => $v['在途數'],
                '調整數' => $v['調整數'],
                '短缺預測' => $v['短缺預測'],
                '供應商' => $v['vendorID'],
                '需求日' => $v['dueDate'],
                '起始日' => $v['startDate'],
                'status' => $v['status'],
                'makeBuy' => $v['makeBuy'],
                '首選供應商' => $v['preferredPo'],
                '採購單' => $v['採購單'],
                '採購單項' => $v['採購單項'],
//                'flag' => $v['lv'],
            ];
        }
        return new Collection($arr);
    }

    public function headings(): array
    {
        return [
            '工單',
            '工單項',
            'pNum',
            'pType',
            'pDes',
            '數量',
            '已發',
            '已收',
            '庫存',
            '在途數',
            '調整數',
            '短缺預測',
            '供應商',
            '需求日',
            '起始日',
            'status',
            'makeBuy',
            '首選供應商',
            '採購單',
            '採購單項',
//            'flag',
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
