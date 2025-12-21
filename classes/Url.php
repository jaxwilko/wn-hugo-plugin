<?php

namespace JaxWilko\Hugo\Classes;

class Url
{
    public static function make(string $base, string $path): string
    {
        return rtrim($base, '/') . ($path ? '/' . ltrim($path, '/') : '');
    }
}
