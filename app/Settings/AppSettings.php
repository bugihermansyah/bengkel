<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public string $brand_name;
    public ?string $brand_logo;
    public ?string $address;
    public ?string $telp;
    public ?string $favicon;


    public static function group(): string
    {
        return 'app';
    }
}