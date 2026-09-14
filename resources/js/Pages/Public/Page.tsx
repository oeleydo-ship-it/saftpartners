import { Head, Link } from '@inertiajs/react';
import { useEffect } from 'react';

const anchors: Record<string, string> = { about: 'about', markets: 'markets', team: 'team', contact: 'contact' };

export default function Page({ page, settings }: { page: string; settings: Record<string, string> }) {
    const key = page.replace(/-/g, '_');
    const title = page.split('-').map(v => v[0].toUpperCase() + v.slice(1)).join(' ');

    // Section pages live on the home page; redirect after render instead of during it.
    useEffect(() => { if (anchors[page]) window.location.replace(`/#${anchors[page]}`); }, [page]);
    if (anchors[page]) return null;

    return <div className="simple-page">
        <Head title={title} />
        <Link href="/" className="simple-logo"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></Link>
        <main>
            <span className="eyebrow">SAF Partners</span>
            <h1>{title}</h1>
            <p>{settings[key] || 'Please contact SAF Partners for more information.'}</p>
            <Link href="/" className="btn btn-primary">Return home</Link>
        </main>
    </div>;
}
