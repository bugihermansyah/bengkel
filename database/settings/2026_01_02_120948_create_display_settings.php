<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('display.brand_name', 'My App');
        $this->migrator->add('display.brand_logo', null);
        $this->migrator->add('display.footer', '© 2026 My Company');
    }
};
