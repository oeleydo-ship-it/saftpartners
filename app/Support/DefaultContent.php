<?php
namespace App\Support;

/**
 * Launch copy for the public site. Used to seed the database and as a runtime
 * fallback so the site never renders empty sections on an unseeded database.
 */
class DefaultContent
{
    public static function settings(): array
    {
        return [
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
    }

    /** @return array<int, array{title: string, slug: string, image: string}> */
    public static function markets(): array
    {
        return [
            ['title' => 'SOVEREIGN WEALTH FUNDS', 'slug' => 'sovereign-wealth-funds', 'image' => '/images/market-sovereign.jpg'],
            ['title' => 'BANKS', 'slug' => 'banks', 'image' => '/images/market-banks.jpg'],
            ['title' => 'INVESTMENT FUNDS', 'slug' => 'investment-funds', 'image' => '/images/market-investment.jpg'],
            ['title' => 'LOCAL & INTERNATIONAL CORPORATES', 'slug' => 'corporates', 'image' => '/images/market-corporates.jpg'],
            ['title' => 'GOVERNMENT ENTITIES', 'slug' => 'government-entities', 'image' => '/images/market-government.jpg'],
        ];
    }
}
