<?php
namespace App\Traits;

use Illuminate\Support\Facades\URL;

trait Hateoas
{
    /**
     * Build a HATEOAS link array in a consistent format.
     * Non-breaking: callers may pass null values and will be filtered out.
     */
    protected function link(string $href = null, string $method = 'GET', string $rel = 'self') : ?array
    {
        if (empty($href)) {
            return null;
        }
        return [
            'href' => $href,
            'method' => $method,
            'rel' => $rel,
        ];
    }

    /**
     * Build a URL relative to the application base if given a path.
     */
    protected function appUrl(string $path) : string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return url($path);
    }
}
