<?php

declare(strict_types=1);

namespace App\Services;

class BarcodeService
{
    /**
     * Generate a valid EAN-13 barcode string.
     *
     * Starts with the given prefix (default '200' for internal use),
     * pads with random digits to 12 characters, then calculates and
     * appends the EAN-13 check digit.
     *
     * @param  string  $prefix  The leading digits for the barcode.
     * @return string A 13-character EAN-13 barcode string.
     */
    public function generateEAN13(string $prefix = '200'): string
    {
        $partial = $prefix;

        while (strlen($partial) < 12) {
            $partial .= random_int(0, 9);
        }

        $partial = substr($partial, 0, 12);

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $partial[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $partial.$checkDigit;
    }

    /**
     * Return the value as-is for Code 128 representation.
     *
     * This is a placeholder for future barcode rendering integration.
     * Code 128 encoding is handled by the barcode renderer, so no
     * check digit calculation is needed for the string itself.
     *
     * @param  string  $value  The raw barcode value.
     * @return string The unmodified value.
     */
    public function generateCode128(string $value): string
    {
        return $value;
    }

    /**
     * Generate an EAN-13 barcode derived from a SKU.
     *
     * Extracts a 3-digit prefix from the SKU's hash and delegates
     * to generateEAN13.
     *
     * @param  string  $sku  The product SKU.
     * @return string A 13-character EAN-13 barcode string.
     */
    public function generateSKUBarcode(string $sku): string
    {
        $hash = abs(crc32($sku));
        $prefix = str_pad((string) ($hash % 1000), 3, '0', STR_PAD_LEFT);

        return $this->generateEAN13($prefix);
    }
}
