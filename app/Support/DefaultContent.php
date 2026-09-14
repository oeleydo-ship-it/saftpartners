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
            'privacy_policy' => self::privacyPolicy(),
            'privacy_policy_updated' => '2026-09-15',
            'terms' => self::terms(),
            'terms_updated' => '2026-09-15',
        ];
    }

    /**
     * Legal copy uses light formatting rendered by Pages/Public/Page.tsx:
     * "## " heading, "### " subheading, "- " bullet, **bold**, blank line between paragraphs.
     */
    public static function privacyPolicy(): string
    {
        return <<<'MD'
SAF Partners ("SAF Partners", "we", "us" or "our") is a boutique executive search firm registered with the Department of Economy and Tourism in Dubai, United Arab Emirates (License 694172). We respect your privacy and are committed to protecting the personal data you share with us. This Privacy Policy explains what personal data we collect through this website and in the course of our executive search services, why we collect it, and the rights you have.

We process personal data in accordance with the applicable laws of the United Arab Emirates, including Federal Decree-Law No. 45 of 2021 on the Protection of Personal Data.

## 1. Who is responsible for your data

SAF Partners is the controller of the personal data described in this policy. You can contact us at any time:

- **Email:** contact@safpartners.ae
- **Post:** SAF Partners, P.O. Box 122465, Dubai, United Arab Emirates

## 2. Personal data we collect

### Information you give us

- **Contact form enquiries:** your name, email address, and optionally your phone number, company, subject and message.
- **Candidate information:** if you contact us about career opportunities, we may receive your CV, employment history, qualifications, compensation expectations and references.
- **Client information:** names, job titles and business contact details of people at organisations that engage us or discuss a mandate with us.

### Information collected automatically

- **Technical data:** your IP address and browser user agent when you submit the contact form. We use this to protect the website against spam and abuse.
- **Essential cookies:** a session cookie and a security (CSRF) token that keep the website secure and working. We do not use advertising or analytics cookies.

## 3. How and why we use your data

- **To respond to your enquiry** and communicate with you, based on your consent and our legitimate interest in replying to messages sent to us.
- **To provide executive search services:** identifying, assessing and presenting suitable candidates to clients, and managing mandates, based on your consent and on steps taken at your request before entering into an engagement.
- **To keep the website secure:** detecting spam, fraud and misuse, based on our legitimate interests.
- **To comply with legal obligations** under UAE law and respond to lawful requests from authorities.

We will never sell your personal data, and we do not use it for automated decision-making. We will not share a candidate's details with a client without first discussing the opportunity with the candidate.

## 4. Confidentiality

Our mandates are confidential. We treat information about candidates, clients and searches as strictly confidential and share it only with people who need it to carry out a search.

## 5. Sharing your data

We share personal data only where necessary with:

- **Clients**, when you are being considered for a role and have agreed to be put forward.
- **Service providers** who host our website, store data or deliver email on our behalf, under contracts that require them to protect it.
- **Font delivery:** this website loads fonts from Bunny Fonts, which receives your IP address to deliver them.
- **Authorities or advisers**, where required by law or to establish, exercise or defend legal claims.

## 6. International transfers

Some of our service providers may store or process data outside the United Arab Emirates. Where this happens, we take steps to ensure your data receives an adequate level of protection, as required by applicable law.

## 7. How long we keep your data

- **Enquiries** are kept for up to 24 months after our last contact, unless they lead to an ongoing relationship.
- **Candidate records** are kept while they remain relevant to future opportunities, and in any case you may ask us to delete them at any time.
- **Security records** (IP address and user agent) are kept only as long as they are needed to protect the website.

## 8. Your rights

Subject to applicable law, you have the right to:

- access the personal data we hold about you;
- ask us to correct inaccurate or incomplete data;
- ask us to delete your data or restrict how we use it;
- object to processing based on our legitimate interests;
- withdraw your consent at any time, without affecting processing already carried out; and
- lodge a complaint with the UAE Data Office.

To exercise any of these rights, email contact@safpartners.ae. We may need to verify your identity before responding.

## 9. Security

We use appropriate technical and organisational measures to protect personal data, including encrypted connections, restricted administrative access and protection against automated abuse. No method of transmission over the internet is completely secure, so please avoid sending highly sensitive information through the contact form.

## 10. Changes to this policy

We may update this Privacy Policy from time to time. The latest version will always be published on this page with its "Last updated" date.
MD;
    }

    public static function terms(): string
    {
        return <<<'MD'
These Terms of Use govern your access to and use of the SAF Partners website. By using this website, you agree to these terms. If you do not agree, please do not use the website.

## 1. About us

This website is operated by SAF Partners, a boutique executive search firm registered with the Department of Economy and Tourism in Dubai, United Arab Emirates (License 694172), P.O. Box 122465, Dubai, United Arab Emirates.

## 2. Purpose of the website

The website provides general information about SAF Partners and our executive search services. Nothing on the website constitutes an offer of employment, a guarantee of placement, or an offer to provide services. Any engagement for executive search services is subject to a separate written agreement.

Our mandates are confidential, and we do not advertise them through third parties or on this website. Be cautious of anyone claiming to recruit on behalf of SAF Partners through other channels; if in doubt, contact us directly at contact@safpartners.ae.

## 3. Using the website

You agree to use the website lawfully and not to:

- submit false, misleading or unlawful information, or impersonate any person;
- send unsolicited advertising, spam or malicious code;
- attempt to gain unauthorised access to the website, its servers or any connected systems; or
- copy, scrape or harvest content or personal data from the website by automated means.

We may restrict or suspend access to the website at any time, including to protect it from abuse.

## 4. Information you submit

When you contact us, you confirm that the information you provide is accurate and that you are entitled to share it. We handle personal data as described in our Privacy Policy.

## 5. Intellectual property

All content on this website, including text, graphics, logos and images, is owned by or licensed to SAF Partners and is protected by intellectual property laws. You may view and print pages for your personal, non-commercial use. You may not reproduce, modify or distribute any content, or use the SAF Partners name or logo, without our prior written permission.

## 6. Accuracy of information

We aim to keep the website accurate and up to date, but the content is provided for general information only and "as is". We make no warranties about its completeness, accuracy or availability, and it should not be relied on as professional advice.

## 7. Third-party links

The website may contain links to websites operated by others. We are not responsible for their content, availability or privacy practices, and a link does not imply our endorsement.

## 8. Limitation of liability

To the fullest extent permitted by law, SAF Partners is not liable for any loss or damage arising from your use of, or inability to use, the website or any content on it, including indirect or consequential loss. Nothing in these terms limits liability that cannot be limited under applicable law.

## 9. Changes to these terms

We may update these Terms of Use from time to time. The latest version will always be published on this page with its "Last updated" date, and your continued use of the website means you accept the updated terms.

## 10. Governing law

These terms are governed by the laws of the Emirate of Dubai and the federal laws of the United Arab Emirates. The courts of Dubai have exclusive jurisdiction over any dispute arising from them.

## 11. Contact

Questions about these terms can be sent to contact@safpartners.ae.
MD;
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
