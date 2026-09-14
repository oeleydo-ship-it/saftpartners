<?php

use Database\Seeders\ContentSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Seeds default site content as part of `php artisan migrate`, so hosts that only
 * run migrations on deploy still get content. Only inserts what is missing.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new ContentSeeder)->run();
    }

    public function down(): void
    {
        // Content may have been edited in the CMS; leave it in place.
    }
};
