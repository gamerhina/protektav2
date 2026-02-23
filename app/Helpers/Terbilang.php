<?php

namespace App\Helpers;

class Terbilang
{
    private static $angka = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas'
    ];

    public static function convert($number)
    {
        if (!is_numeric($number)) {
            return 'Input harus berupa angka';
        }

        $number = floatval($number);
        
        if ($number < 0) {
            return 'minus ' . self::convert(abs($number));
        }

        if ($number == 0) {
            return 'nol';
        }

        $integerPart = floor($number);
        $decimalString = substr(strval($number), strpos(strval($number), '.') + 1);

        $result = self::convertInteger($integerPart);

        if (strpos(strval($number), '.') !== false && $decimalString) {
            $result .= ' koma';
            // Convert each decimal digit individually
            for ($i = 0; $i < strlen($decimalString); $i++) {
                $digit = intval($decimalString[$i]);
                $result .= ' ' . self::$angka[$digit];
            }
        }

        return $result;
    }

    private static function convertInteger($number)
    {
        if ($number < 12) {
            return self::$angka[$number];
        }

        if ($number < 20) {
            return self::$angka[$number - 10] . ' belas';
        }

        if ($number < 100) {
            $tens = floor($number / 10);
            $ones = $number % 10;
            return self::$angka[$tens] . ' puluh' . ($ones > 0 ? ' ' . self::$angka[$ones] : '');
        }

        if ($number < 200) {
            $remainder = $number % 100;
            return 'seratus' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        if ($number < 1000) {
            $hundreds = floor($number / 100);
            $remainder = $number % 100;
            return self::$angka[$hundreds] . ' ratus' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        if ($number < 2000) {
            $remainder = $number % 1000;
            return 'seribu' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        if ($number < 1000000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            return self::convertInteger($thousands) . ' ribu' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        if ($number < 1000000000) {
            $millions = floor($number / 1000000);
            $remainder = $number % 1000000;
            return self::convertInteger($millions) . ' juta' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        if ($number < 1000000000000) {
            $billions = floor($number / 1000000000);
            $remainder = $number % 1000000000;
            return self::convertInteger($billions) . ' milyar' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        return 'Angka terlalu besar';
    }

    public static function toHuruf($number)
    {
        $number = (float) $number;
        if ($number >= 80) return 'A';
        if ($number >= 75) return 'B+';
        if ($number >= 70) return 'B';
        if ($number >= 65) return 'C+';
        if ($number >= 60) return 'C';
        if ($number >= 50) return 'D';
        return 'E';
    }
}
