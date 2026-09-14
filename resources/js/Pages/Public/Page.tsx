import { Head, Link } from '@inertiajs/react';
import { ReactNode, useEffect } from 'react';

const anchors: Record<string, string> = { about: 'about', markets: 'markets', team: 'team', contact: 'contact' };

/** Turns **bold** and email addresses into elements. Text is never injected as HTML. */
function inline(text: string): ReactNode[] {
    return text.split(/(\*\*[^*]+\*\*|[\w.+-]+@[\w-]+\.[\w.]+)/g).filter(Boolean).map((part, i) => {
        if (part.startsWith('**') && part.endsWith('**')) return <strong key={i}>{part.slice(2, -2)}</strong>;
        if (/^[\w.+-]+@[\w-]+\.[\w.]+$/.test(part)) return <a key={i} href={`mailto:${part}`}>{part}</a>;
        return part;
    });
}

/** Renders CMS legal copy: "## " heading, "### " subheading, "- " bullets, blank line between paragraphs. */
function LegalDocument({ text }: { text: string }) {
    const blocks = text.replace(/\r\n/g, '\n').split(/\n{2,}/).map(block => block.trim()).filter(Boolean);

    return <div className="legal-doc">{blocks.map((block, i) => {
        if (block.startsWith('### ')) return <h3 key={i}>{inline(block.slice(4))}</h3>;
        if (block.startsWith('## ')) return <h2 key={i}>{inline(block.slice(3))}</h2>;
        const lines = block.split('\n');
        if (lines.every(line => line.startsWith('- '))) return <ul key={i}>{lines.map((line, j) => <li key={j}>{inline(line.slice(2))}</li>)}</ul>;
        return <p key={i}>{lines.flatMap((line, j) => j ? [<br key={`br${j}`} />, ...inline(line)] : inline(line))}</p>;
    })}</div>;
}

const formatDate = (value?: string) => {
    if (!value) return null;
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
};

export default function Page({ page, settings }: { page: string; settings: Record<string, string> }) {
    const key = page.replace(/-/g, '_');
    const title = page === 'terms' ? 'Terms of Use' : page.split('-').map(v => v[0].toUpperCase() + v.slice(1)).join(' ');
    const updated = formatDate(settings[`${key}_updated`]);

    // Section pages live on the home page; redirect after render instead of during it.
    useEffect(() => { if (anchors[page]) window.location.replace(`/#${anchors[page]}`); }, [page]);
    if (anchors[page]) return null;

    return <div className="simple-page">
        <Head title={title}>
            <meta name="robots" content="noindex, follow" />
        </Head>
        <Link href="/" className="simple-logo"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></Link>
        <main className="legal-card">
            <span className="eyebrow">SAF Partners</span>
            <h1>{title}</h1>
            {updated && <p className="legal-updated">Last updated: {updated}</p>}
            {settings[key] ? <LegalDocument text={settings[key]} /> : <p>Please contact SAF Partners for more information.</p>}
            <nav className="legal-footer">
                <Link href="/" className="btn btn-primary">Return home</Link>
                <Link href={page === 'terms' ? '/privacy-policy' : '/terms'}>{page === 'terms' ? 'Privacy Policy' : 'Terms of Use'}</Link>
            </nav>
        </main>
    </div>;
}
