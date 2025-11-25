<?php

namespace Meita\ZatcaEInvoice\Support;

class XmlBuilder
{
    public static function build(array $header, array $seller, array $buyer, array $items, array $totals): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // Root Invoice element
        $invoice = $doc->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            'Invoice'
        );

        // Add namespaces
        $invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:cac',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
        );

        $invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:cbc',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
        );

        $doc->appendChild($invoice);

        // Header
        self::addCbc($doc, $invoice, 'cbc:ID', $header['number']);
        self::addCbc($doc, $invoice, 'cbc:IssueDate', $header['date']);
        self::addCbc($doc, $invoice, 'cbc:IssueTime', $header['time']);

        // Seller
        $supplierParty = self::cac($doc, $invoice, 'cac:AccountingSupplierParty');
        $party = self::cac($doc, $supplierParty, 'cac:Party');
        self::addCbc($doc, $party, 'cbc:Name', $seller['name']);
        $taxScheme = self::cac($doc, $party, 'cac:PartyTaxScheme');
        self::addCbc($doc, $taxScheme, 'cbc:CompanyID', $seller['vat']);

        // Buyer
        $customerParty = self::cac($doc, $invoice, 'cac:AccountingCustomerParty');
        $party2 = self::cac($doc, $customerParty, 'cac:Party');
        self::addCbc($doc, $party2, 'cbc:Name', $buyer['name']);
        $buyerTax = self::cac($doc, $party2, 'cac:PartyTaxScheme');
        self::addCbc($doc, $buyerTax, 'cbc:CompanyID', $buyer['vat']);

        // Invoice lines
        foreach ($items as $index => $item) {
            $line = self::cac($doc, $invoice, 'cac:InvoiceLine');
            self::addCbc($doc, $line, 'cbc:ID', $index + 1);
            self::addCbc($doc, $line, 'cbc:InvoicedQuantity', $item['qty']);

            $amount = number_format($item['amount'], 2, '.', '');
            $vat    = number_format($item['vat_amount'], 2, '.', '');

            $extAmount = $doc->createElement('cbc:LineExtensionAmount', $amount);
            $extAmount->setAttribute('currencyID', 'SAR');
            $line->appendChild($extAmount);

            $price = self::cac($doc, $line, 'cac:Price');
            $priceAmount = $doc->createElement('cbc:PriceAmount', $amount);
            $priceAmount->setAttribute('currencyID', 'SAR');
            $price->appendChild($priceAmount);

            $tax = self::cac($doc, $line, 'cac:TaxTotal');
            $taxSub = self::cac($doc, $tax, 'cac:TaxSubtotal');
            $taxAmount = $doc->createElement('cbc:TaxAmount', $vat);
            $taxAmount->setAttribute('currencyID', 'SAR');
            $taxSub->appendChild($taxAmount);
        }

        // Totals
        $legal = self::cac($doc, $invoice, 'cac:LegalMonetaryTotal');

        $sub = number_format($totals['subtotal'], 2, '.', '');
        $vat = number_format($totals['vat'], 2, '.', '');
        $total = number_format($totals['total'], 2, '.', '');

        self::addCbcCurrency($doc, $legal, 'cbc:TaxExclusiveAmount', $sub);
        self::addCbcCurrency($doc, $legal, 'cbc:TaxInclusiveAmount', $total);
        self::addCbcCurrency($doc, $legal, 'cbc:PayableAmount', $total);

        return $doc->saveXML();
    }

    private static function cac($doc, $parent, $name)
    {
        $el = $doc->createElement($name);
        $parent->appendChild($el);
        return $el;
    }

    private static function addCbc($doc, $parent, $name, $value)
    {
        $el = $doc->createElement($name, $value);
        $parent->appendChild($el);
        return $el;
    }

    private static function addCbcCurrency($doc, $parent, $name, $value)
    {
        $el = $doc->createElement($name, $value);
        $el->setAttribute('currencyID', 'SAR');
        $parent->appendChild($el);
        return $el;
    }
}
