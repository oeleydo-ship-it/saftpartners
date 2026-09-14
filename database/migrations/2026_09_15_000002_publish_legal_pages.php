<?php

use App\Models\SiteSetting;
use App\Support\DefaultContent;
use Database\Seeders\ContentSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Replaces the original one-line privacy/terms placeholders with the full legal pages.
 * Text that has been edited in the admin CMS is left untouched.
 */
return new class extends Migration
{
    private const PLACEHOLDERS = [
        'privacy_policy' => 'Your privacy matters to us. Contact SAF Partners for details about how enquiries are handled.',
        'terms' => 'Use of this website is subject to applicable UAE law.',
    ];

    public function up(): void
    {
        foreach (self::PLACEHOLDERS as $key => $placeholder) {
            SiteSetting::where('key', $key)
                ->where(fn ($query) => $query->where('value', $placeholder)->orWhereNull('value')->orWhere('value', ''))
                ->update(['value' => DefaultContent::settings()[$key]]);
        }

        // Adds the new "last updated" settings.
        (new ContentSeeder)->run();
    }

    public function down(): void
    {
        // Legal text may have been edited in the CMS; leave it in place.
    }
};
