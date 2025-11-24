<?php

namespace Meita\ZatcaEInvoice\Contracts;

interface InvoiceInterface
{
    public function setHeader(array $header): self;

    public function setSeller(array $seller): self;

    public function setBuyer(array $buyer): self;

    public function addItem(array $item): self;

    public function setTotals(array $totals): self;

    public function generateXml(): string;

    public function generateQr(): string;

    public function generateFullPayload(): array;

    public function stamp(): string;

    public function sendToZatca(string $mode = 'reporting'): array;
}
