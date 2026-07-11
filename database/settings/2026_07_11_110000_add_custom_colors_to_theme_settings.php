<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.primary_color', '#ff9b44');
        $this->migrator->add('theme.font_color', '#1f1f1f');
    }
};
