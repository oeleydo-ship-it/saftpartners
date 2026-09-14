<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Market;
use App\Models\SiteSetting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Safe to run on every deployment: it only creates records that are missing,
 * so content edited in the admin CMS and existing passwords are never overwritten.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedAdmin();

        $settings = [
            'site_name' => 'SAF PARTNERS',
            'hero_title' => 'SAF PARTNERS',
            'hero_subtitle' => 'AN EXECUTIVE SEARCH FIRM RECRUITING TOP EMIRATI TALENT',
            'hero_cta_label' => 'HIRE NOW',
            'hero_cta_url' => 'https://www.safhyre.com/homepage',
            'about_heading' => 'ABOUT US',
            'about_1' => 'SAF Partners is a boutique Executive Search Firm that specializes in recruiting executive, senior and middle management Emiratis for investment, corporate, finance and operations positions.',
            'about_2' => 'SAF Partners was established in 2013, by an Emirati former banker with two of the leading international banks in the UAE and Singapore, with a vision to assist companies in the UAE to attract the right UAE National talent.',
            'about_3' => 'We have a strong network of mid to senior level UAE Nationals with expertise in investment management, finance, treasury, audit, IT, HR and other support functions.',
            'markets_heading' => 'MARKETS',
            'markets_intro' => 'SAF Partners focuses on mandates at the mid to senior level with the following types of institutions',
            'markets_footer' => "We have completed mandates across Consumer Banking, Corporate and Investment Banking, Treasury, Technology, Finance, Audit and Strategy.\nWe also execute select retained searches for executive positions.",
            'team_heading' => 'TEAM',
            'team_text_1' => 'Our Managing Partners have over 20 years of experience across private equity, investment advisory and executive search with international banks in the Middle East, Asia and Africa.',
            'team_text_2' => 'They are the primary interface between clients and candidates throughout the entire search process. We give our clients the competitive advantage to find the best quality Emiratis for the right position in their organization and to improve the ability to identify and attract the right fit candidates.',
            'contact_heading' => 'CONTACT US',
            'contact_intro' => 'Our mandates are confidential and we do not advertise them through third parties or on our website.',
            'contact_address' => "P.O.Box 122465\nDubai, UAE\ncontact@safpartners.ae\nSAF PARTNERS is a DED Registered Firm, License 694172",
            'footer_text' => 'SAF Partners | All Rights Reserved',
            'privacy_policy' => 'Your privacy matters to us. Contact SAF Partners for details about how enquiries are handled.',
            'terms' => 'Use of this website is subject to applicable UAE law.',
        ];
        foreach ($settings as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => str($key)->before('_')]);
        }

        $markets = [
            ['SOVEREIGN WEALTH FUNDS', 'sovereign-wealth-funds', '/images/market-sovereign.jpg'],
            ['BANKS', 'banks', '/images/market-banks.jpg'],
            ['INVESTMENT FUNDS', 'investment-funds', '/images/market-investment.jpg'],
            ['LOCAL & INTERNATIONAL CORPORATES', 'corporates', '/images/market-corporates.jpg'],
            ['GOVERNMENT ENTITIES', 'government-entities', '/images/market-government.jpg'],
        ];
        foreach ($markets as $order => [$title, $slug, $image]) {
            // withTrashed: a market archived in the admin must not be re-created.
            if (! Market::withTrashed()->where('slug', $slug)->exists()) {
                Market::create(['slug' => $slug, 'title' => $title, 'image' => $image, 'sort_order' => $order, 'is_active' => true]);
            }
        }
    }

    private function seedAdmin(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@safpartners.ae');
        $password = env('ADMIN_PASSWORD');

        if (User::where('email', $email)->exists()) return;

        if (blank($password)) {
            if (app()->isProduction()) {
                $this->command?->warn('ADMIN_PASSWORD is not set: skipping admin account creation.');
                return;
            }
            $password = 'ChangeMe123!';
        }

        User::create([
            'name' => 'SAF Partners Administrator',
            'email' => $email,
            'password' => $password,
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);
    }
}
