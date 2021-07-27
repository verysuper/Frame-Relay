<?php

namespace App\Excel\Exports\ERP;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class LogPoRcvExport implements FromCollection, WithHeadings, WithEvents
{
    private $list;

    public function __construct($list)
    {
        $this->list = $list;
    }

    public function collection(): Collection
    {
        return new Collection($this->list);
    }

    public function headings(): array
    {
        return [
            'pn',
            'pa',
            'in_qty',
            'out_qty',
            'ref_doc1',
            'ref_doc2',
            'costPerUnit',
            'currency',
            'exchangeRate',
            'vendorId',
            'vendorName',
            'taxRate',
            'porStatus',
            'invoice',
            'invoiceDate',
            'poiStatus',
            'demandQty',
            'receivedQty',
            'rcvDate',
            'user',
            'rcvMonth',
            'description',
            'ap_total',
            'ap_tax',
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
