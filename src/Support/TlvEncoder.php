<?php

namespace Meita\ZatcaEInvoice\Support;

class TlvEncoder
{
    public static function encode(int $tag, string $value): string
    {
        return chr($tag) . chr(strlen($value)) . $value;
    }

    public static function encodeInvoice(array $data): string
    {
        $tlv = '';
        $tlv .= self::encode(1, $data['seller_name'] ?? '');
        $tlv .= self::encode(2, $data['seller_vat'] ?? '');
        $tlv .= self::encode(3, $data['timestamp'] ?? '');
        $tlv .= self::encode(4, (string)($data['total'] ?? '0'));
        $tlv .= self::encode(5, (string)($data['vat'] ?? '0'));

        return base64_encode($tlv);
    }
}
