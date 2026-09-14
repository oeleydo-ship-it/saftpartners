<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\SiteSetting;
use App\Support\DefaultContent;
use Illuminate\Database\Seeder;

/**
 * Inserts missing default site content. Never overwrites content edited in the admin CMS.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DefaultContent::settings() as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => str($key)->before('_')]);
        }

        foreach (DefaultContent::markets() as $order => $market) {
            // withTrashed: a market archived in the admin must not be re-created.
            if (! Market::withTrashed()->where('slug', $market['slug'])->exists()) {
                Market::create([...$market, 'sort_order' => $order, 'is_active' => true]);
            }
        }
    }
}
