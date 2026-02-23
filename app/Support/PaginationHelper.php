<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaginationHelper
{
    public static function resolvePerPage(Request $request, int $default = 15, int $max = 10000, string $param = 'per_page'): int
    {
        $choice = $request->input($param, $default);
        $custom = $request->input($param . '_custom');

        // Handle 'all' option
        if ($choice === 'all') {
            return $max;
        }

        if ($choice === 'custom') {
            $value = is_numeric($custom) ? (int) $custom : $default;
        } else {
            $value = is_numeric($choice) ? (int) $choice : $default;
        }

        $value = max(1, min($max, $value));

        return $value;
    }
}
