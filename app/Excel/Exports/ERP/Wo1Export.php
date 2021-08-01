<?php

namespace App\Excel\Exports\ERP;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Files\LocalTemporaryFile;

class Wo1Export implements FromCollection, WithEvents, ShouldAutoSize, WithCustomStartCell
{
    private $list;
    private $calledByEvent = false;

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
                'flag' => $v['lv'],
            ];
        }
        if ($this->calledByEvent) { // flag
            return new Collection($arr);
        }

        return new Collection([]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $templateFile = new LocalTemporaryFile(storage_path('excel_template\工單調度.xlsx'));
                $event->writer->reopen($templateFile, Excel::XLSX);
                $event->writer->getSheetByIndex(0);
                $this->calledByEvent = true; // set the flag
                $event->writer->getSheetByIndex(0)->export($event->getConcernable()); // call the export on the first sheet
                return $event->getWriter()->getSheetByIndex(0);
            },
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }
}
