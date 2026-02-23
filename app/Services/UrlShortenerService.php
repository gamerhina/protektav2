<?php

namespace App\Services;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class UrlShortenerService
{
    /**
     * Shorten a URL and return the short URL.
     */
    public function shorten(string $url): string
    {
        // Check if URL already shortened
        $existing = ShortUrl::where('original_url', $url)->first();
        if ($existing) {
            return route('v', ['code' => $existing->code]);
        }

        // Generate a unique 6-character code
        do {
            $code = Str::random(6);
        } while (ShortUrl::where('code', $code)->exists());

        ShortUrl::create([
            'code' => $code,
            'original_url' => $url
        ]);

        return route('v', ['code' => $code]);
    }

    /**
     * Get the original URL from a code.
     */
    public function getOriginalUrl(string $code): ?string
    {
        return ShortUrl::where('code', $code)->first()?->original_url;
    }
}
