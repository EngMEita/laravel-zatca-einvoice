<?php

namespace Meita\ZatcaEInvoice\Support;

use SimpleXMLElement;

class XmlBuilder
{
    public static function build(array $header, array $seller, array $buyer, array $items, array $totals): string
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
</Invoice>'
        );

        // Header
        $xml->addChild('cbc:ID', $header['number'], 'cbc');
        $xml->addChild('cbc:IssueDate', $header['date'], 'cbc');
        $xml->addChild('cbc:IssueTime', $header['time'], 'cbc');

        // Seller
        $supplier = $xml->addChild('cac:AccountingSupplierParty', null, 'cac')
            ->addChild('cac:Party', null, 'cac');

        $supplier->addChild('cbc:Name', $seller['name'], 'cbc');
        $supplierTax = $supplier->addChild('cac:PartyTaxScheme', null, 'cac');
        $supplierTax->addChild('cbc:CompanyID', $seller['vat'], 'cbc');

        // Buyer
        $customer = $xml->addChild('cac:AccountingCustomerParty', null, 'cac')
            ->addChild('cac:Party', null, 'cac');

        if (!empty($buyer['name'])) {
            $customer->addChild('cbc:Name', $buyer['name'], 'cbc');
        }
        if (!empty($buyer['vat'])) {
            $buyerTax = $customer->addChild('cac:PartyTaxScheme', null, 'cac');
            $buyerTax->addChild('cbc:CompanyID', $buyer['vat'], 'cbc');
        }

        // Items
        foreach ($items as $item) {
            $line = $xml->addChild('cac:InvoiceLine', null, 'cac');
            $line->addChild('cbc:ID', $item['id'], 'cbc');
            $line->addChild('cbc:InvoicedQuantity', $item['qty'], 'cbc');
            $line->addChild('cbc:LineExtensionAmount', $item['amount'], 'cbc')
                ->addAttribute('currencyID', 'SAR');

            $price = $line->addChild('cac:Price', null, 'cac');
            $price->addChild('cbc:PriceAmount', $item['unit_price'], 'cbc')
                ->addAttribute('currencyID', 'SAR');

            $taxTotal = $line->addChild('cac:TaxTotal', null, 'cac');
            $tax = $taxTotal->addChild('cac:TaxSubtotal', null, 'cac');
            $tax->addChild('cbc:TaxAmount', $item['vat_amount'], 'cbc')
                ->addAttribute('currencyID', 'SAR');
        }

        // Totals
        $total = $xml->addChild('cac:LegalMonetaryTotal', null, 'cac');
        $total->addChild('cbc:TaxExclusiveAmount', $totals['subtotal'], 'cbc')
            ->addAttribute('currencyID', 'SAR');
        $total->addChild('cbc:TaxInclusiveAmount', $totals['total'], 'cbc')
            ->addAttribute('currencyID', 'SAR');
        $total->addChild('cbc:PayableAmount', $totals['total'], 'cbc')
            ->addAttribute('currencyID', 'SAR');

        return $xml->asXML();
    }
}
