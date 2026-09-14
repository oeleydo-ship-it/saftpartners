import { Head, Link } from '@inertiajs/react';

export default function Page({ page, settings }: { page: string; settings: Record<string, string> }) {
    const key = page.replace('-', '_');
    const title = page.split('-').map(v => v[0].toUpperCase() + v.slice(1)).join(' ');
    const anchors: Record<string, string> = { about: 'about', markets: 'markets', team: 'team', contact: 'contact' };
    if (anchors[page]) { window.location.replace(`/#${anchors[page]}`); return null; }
    return <div className="simple-page"><Head title={title} /><Link href="/"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></Link><main><h1>{title}</h1><p>{settings[key] || 'Please contact SAF Partners for more information.'}</p><Link href="/">Return home</Link></main></div>;
}
