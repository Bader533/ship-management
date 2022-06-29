<?php

namespace App\Exports;

use App\Models\Shippment;
use Maatwebsite\Excel\Concerns\FromCollection;

class ShippmentsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // public function collection()
    // {
    //     return Shippment::all();
    // }
    // public $from;
    // public $to;

    // function __construct($from, $to)
    // {
    //     $this->from = $from;
    //     $this->to = $to;
    // }

    public function collection()
    {
        return;
    }
    public function headings(): array
    {
        return [
            'shippment_type',
            'shipper_name',
            'area',
            'area',
            'business_referance',
            'receiver_name',
            'shippment_type',
            'phone_number',
            'allow_open',
            'price',
            'package_details',
            'address',
            'note',
            '*.status',
        ];
    }
}
