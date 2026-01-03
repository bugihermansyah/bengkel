<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PrintSettings extends Settings
{
    public string $brand_name;
    public ?string $header_1;
    public ?string $header_2;
    public ?string $footer_1;
    public ?string $footer_2;
    public ?string $footer_3;
    public string $printer_name;
    public string $paper_size;

    public static function group(): string
    {
        return 'print';
    }
}