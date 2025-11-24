<?php

namespace Meita\ZatcaEInvoice;

use Illuminate\Support\ServiceProvider;
use Meita\ZatcaEInvoice\Contracts\InvoiceInterface;
use Meita\ZatcaEInvoice\Services\ZatcaInvoice;

class ZatcaInvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/zatca.php', 'zatca');

        $this->app->bind(InvoiceInterface::class, ZatcaInvoice::class);

        $this->app->singleton('zatca-invoice', function ($app) {
            return $app->make(ZatcaInvoice::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/zatca.php' => config_path('zatca.php'),
        ], 'zatca-config');
    }
}
