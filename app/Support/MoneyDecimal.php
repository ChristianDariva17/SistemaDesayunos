<?php

declare(strict_types=1);

namespace App\Support;

final class MoneyDecimal
{
    public static function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absoluteCents = abs($cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absoluteCents, 100), $absoluteCents % 100);
    }

    public static function toCents(string|int|float|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $normalized = trim((string) $amount);
        $negative = str_starts_with($normalized, '-');

        if ($negative) {
            $normalized = substr($normalized, 1);
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $whole = preg_replace('/\D/', '', $whole) ?: '0';
        $fraction = preg_replace('/\D/', '', $fraction) ?: '0';

        $thirdDecimal = (int) ($fraction[2] ?? '0');
        $cents = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        if ($thirdDecimal >= 5) {
            $cents++;
        }

        return $negative ? -$cents : $cents;
    }

    public static function multiply(string|int|float $amount, int $quantity): string
    {
        return self::fromCents(self::toCents($amount) * $quantity);
    }

    public static function sum(iterable $amounts): string
    {
        $totalCents = 0;

        foreach ($amounts as $amount) {
            $totalCents += self::toCents($amount);
        }

        return self::fromCents($totalCents);
    }

    public static function divide(string|int|float $amount, int $divisor): string
    {
        if ($divisor <= 0) {
            throw new \InvalidArgumentException('Money divisor must be greater than zero.');
        }

        $cents = self::toCents($amount);
        $sign = $cents < 0 ? -1 : 1;
        $absoluteCents = abs($cents);
        $roundedCents = intdiv($absoluteCents, $divisor);

        if (($absoluteCents % $divisor) * 2 >= $divisor) {
            $roundedCents++;
        }

        return self::fromCents($roundedCents * $sign);
    }

    /** @return array{net:string,tax:string,gross:string} */
    public static function splitInclusiveTax(string|int|float $gross, int $taxRate): array
    {
        $grossCents = self::toCents($gross);
        $net = self::divide(self::fromCents($grossCents * 100), 100 + $taxRate);
        $netCents = self::toCents($net);

        return [
            'net' => $net,
            'tax' => self::fromCents($grossCents - $netCents),
            'gross' => self::fromCents($grossCents),
        ];
    }
}
