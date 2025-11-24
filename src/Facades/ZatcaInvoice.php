<?php

namespace Meita\ZatcaEInvoice\Facades;

use Illuminate\Support\Facades\Facade;

class ZatcaInvoice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zatca-invoice';
    }
}
