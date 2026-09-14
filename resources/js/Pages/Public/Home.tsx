import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useEffect, useState } from 'react';

type Market = { id: number; title: string; description?: string; image?: string; icon?: string };
type Team = { id: number; name: string; position: string; biography?: string; photo?: string };
type Props = { settings: Record<string, string>; markets: Market[]; team: Team[] };

const sectionTitle = (text: string) => <h2 className="section-title"><span />{text}<span /></h2>;

export default function Home({ settings: s, markets, team }: Props) {
    const [menuOpen, setMenuOpen] = useState(false);
    const { flash } = usePage<any>().props as { flash?: { success?: string } };
    const form = useForm({ name: '', email: '', phone: '', company: '', subject: '', message: '', consent: true, website: '' });
    useEffect(() => {
        const close = (event: KeyboardEvent) => event.key === 'Escape' && setMenuOpen(false);
        window.addEventListener('keydown', close);
        document.body.style.overflow = menuOpen ? 'hidden' : '';
        return () => { window.removeEventListener('keydown', close); document.body.style.overflow = ''; };
    }, [menuOpen]);
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('contact.store'), { preserveScroll: true, onSuccess: () => form.reset() });
    };
    const nav = [['HOME', '#home'], ['ABOUT US', '#about'], ['HIRE NOW', s.hero_cta_url || '#contact'], ['MARKETS', '#markets'], ['TEAM', '#team'], ['CONTACT', '#contact']];

    return <div className="public-site">
        <Head title="Executive Search for Emirati Talent">
            <meta name="description" content={s.hero_subtitle} />
            <meta property="og:title" content={s.site_name || 'SAF Partners'} />
            <link rel="canonical" href={window.location.origin} />
        </Head>
        <a href="#about" className="skip-link">Skip to content</a>
        <header id="home" className="hero">
            <nav className="nav-shell" aria-label="Main navigation">
                <a href="#home" aria-label="SAF Partners home"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></a>
                <button className="menu-button" onClick={() => setMenuOpen(!menuOpen)} aria-expanded={menuOpen} aria-controls="mobile-menu"><span /><span /><span /><span className="sr-only">Menu</span></button>
                <div id="mobile-menu" className={`nav-links ${menuOpen ? 'open' : ''}`}>
                    {nav.map(([label, href]) => <a key={label} href={href} target={href.startsWith('http') ? '_blank' : undefined} rel="noreferrer" onClick={() => setMenuOpen(false)}>{label}</a>)}
                </div>
            </nav>
            <div className="hero-copy">
                <img src="/images/saf-partners-logo.png" alt="SAF Partners" className="hero-logo" />
                <p>{s.hero_subtitle}</p>
            </div>
        </header>

        <main>
            <section id="about" className="about-section">
                <div className="about-card">
                    {sectionTitle(s.about_heading || 'ABOUT US')}
                    <div className="about-grid">
                        {[s.about_1, s.about_2, s.about_3].map((text, i) => <article key={i}>
                            <div className={`about-icon about-icon-${i + 1}`} aria-hidden="true">{i === 0 ? '✥' : i === 1 ? '♙' : '⌕'}</div>
                            <i className="timeline-dot" />
                            <p>{text}</p>
                        </article>)}
                    </div>
                </div>
            </section>

            <section id="markets" className="markets-section">
                {sectionTitle(s.markets_heading || 'MARKETS')}
                <p className="section-intro">{s.markets_intro}</p>
                <div className="market-track">
                    {markets.map((market) => <article className="market-item" key={market.id}>
                        <span className="market-circle"><img src={market.image || market.icon} alt="" /></span>
                        <h3>{market.title}</h3>
                    </article>)}
                </div>
                <p className="market-footer">{s.markets_footer}</p>
            </section>

            <section id="team" className="team-section">
                {sectionTitle(s.team_heading || 'TEAM')}
                <p>{s.team_text_1}</p>
                <p>{s.team_text_2}</p>
                {team.length > 0 && <div className="team-grid">{team.map(member => <article key={member.id}>{member.photo && <img src={member.photo} alt={member.name} />}<h3>{member.name}</h3><strong>{member.position}</strong><p>{member.biography}</p></article>)}</div>}
            </section>

            <section id="contact" className="contact-section">
                <div className="contact-heading">{sectionTitle(s.contact_heading || 'CONTACT US')}<p>{s.contact_intro}</p></div>
                <div className="contact-grid">
                    <div className="contact-address"><p>{s.contact_address}</p></div>
                    <form onSubmit={submit} noValidate aria-label="Contact form">
                        {flash?.success && <div className="success-message" role="status">{flash.success}</div>}
                        <input className="honeypot" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={e => form.setData('website', e.target.value)} aria-hidden="true" />
                        {[['name', 'Your Name (required)', 'text'], ['email', 'Your Email (required)', 'email'], ['phone', 'Phone', 'tel'], ['company', 'Company', 'text'], ['subject', 'Subject', 'text']].map(([name, label, type]) => <label key={name}>{label}<input type={type} value={(form.data as any)[name]} onChange={e => form.setData(name as any, e.target.value)} aria-invalid={!!(form.errors as any)[name]} />{(form.errors as any)[name] && <small>{(form.errors as any)[name]}</small>}</label>)}
                        <label>Your Message<textarea rows={6} value={form.data.message} onChange={e => form.setData('message', e.target.value)} aria-invalid={!!form.errors.message} />{form.errors.message && <small>{form.errors.message}</small>}</label>
                        <label className="consent"><input type="checkbox" checked={form.data.consent} onChange={e => form.setData('consent', e.target.checked)} /> I consent to SAF Partners using this information to respond to my enquiry.</label>
                        <button type="submit" disabled={form.processing}>{form.processing ? 'SENDING…' : 'S E N D'}</button>
                    </form>
                </div>
            </section>
        </main>
        <footer><span>© {new Date().getFullYear()} {s.footer_text}</span><div><Link href="/privacy-policy">Privacy</Link><Link href="/terms">Terms</Link><a href="#home" aria-label="Back to top">⌃</a></div></footer>
    </div>;
}
