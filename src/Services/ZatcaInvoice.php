<?php

namespace Meita\ZatcaEInvoice\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Meita\ZatcaEInvoice\Contracts\InvoiceInterface;
use Meita\ZatcaEInvoice\Support\TlvEncoder;
use Meita\ZatcaEInvoice\Support\XmlBuilder;

class ZatcaInvoice implements InvoiceInterface
{
    protected array $header = [];
    protected array $seller = [];
    protected array $buyer = [];
    protected array $items = [];
    protected array $totals = [];

    public function __construct()
    {
        $this->seller = Config::get('zatca.seller', []);
    }

    public function setHeader(array $header): self
    {
        $this->header = $header;
        return $this;
    }

    public function setSeller(array $seller): self
    {
        $this->seller = array_merge($this->seller, $seller);
        return $this;
    }

    public function setBuyer(array $buyer): self
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function addItem(array $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    public function setTotals(array $totals): self
    {
        $this->totals = $totals;
        return $this;
    }

    public function generateXml(): string
    {
        return XmlBuilder::build($this->header, $this->seller, $this->buyer, $this->items, $this->totals);
    }

    public function generateQr(): string
    {
        return TlvEncoder::encodeInvoice([
            'seller_name' => $this->seller['name'] ?? '',
            'seller_vat'  => $this->seller['vat'] ?? '',
            'timestamp'   => $this->header['timestamp'] ?? '',
            'total'       => $this->totals['total'] ?? 0,
            'vat'         => $this->totals['vat'] ?? 0,
        ]);
    }

    public function generateFullPayload(): array
    {
        return [
            'xml' => $this->generateXml(),
            'qr'  => $this->generateQr(),
        ];
    }

    public function stamp(): string
    {
        // Placeholder – في الواقع هتستخدم Private Key + CSID بالـ ECC
        return base64_encode(hash('sha256', $this->generateXml(), true));
    }

    public function sendToZatca(string $mode = 'reporting'): array
    {
        $env = Config::get('zatca.environment', 'sandbox');
        $endpoints = Config::get("zatca.endpoints.{$env}");

        $url = $endpoints[$mode] ?? null;

        if (!$url) {
            throw new \RuntimeException("Invalid ZATCA endpoint for mode [{$mode}].");
        }

        $token = Config::get('zatca.token');
        if (!$token) {
            throw new \RuntimeException("ZATCA token not configured.");
        }

        $xml = $this->generateXml();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Authorization' => 'Bearer ' . $token,
        ])->post($url, [
            'invoice' => base64_encode($xml),
        ]);

        return $response->json();
    }
}
