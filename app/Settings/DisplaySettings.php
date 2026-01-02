<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DisplaySettings extends Settings
{
    public string $brand_name;
    public ?string $brand_logo;
    public ?string $footer;

    public static function group(): string
    {
        return 'display';
    }
}