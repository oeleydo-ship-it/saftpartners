import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent, ReactNode, useEffect, useState } from 'react';

type Market = { id: number; title: string; description?: string; image?: string; icon?: string };
type Team = { id: number; name: string; position: string; biography?: string; photo?: string };
type Props = { settings: Record<string, string>; markets: Market[]; team: Team[]; formToken: string };
type Field = 'name' | 'email' | 'phone' | 'company' | 'subject' | 'message';

const SectionTitle = ({ children, eyebrow }: { children: ReactNode; eyebrow?: string }) => <div className="section-heading">
    {eyebrow && <span className="eyebrow">{eyebrow}</span>}
    <h2 className="section-title">{children}</h2>
</div>;

const icons = [
    // Network
    <svg key="network" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2.5" /><circle cx="5" cy="18" r="2.5" /><circle cx="19" cy="18" r="2.5" /><path d="M11 7.2 6.2 15.8M13 7.2l4.8 8.6M7.5 18h9" /></svg>,
    // Leadership
    <svg key="leader" viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5" /><path d="M5 20.5c.8-4 3.5-6 7-6s6.2 2 7 6" /></svg>,
    // Search
    <svg key="search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6" /><path d="m15 15 5.5 5.5" /></svg>,
];
const aboutLabels = ['Who we are', 'Our story', 'Our network'];

/** Splits free-text address into lines and turns emails into mailto links. */
const addressLines = (text = '') => text.split('\n').filter(Boolean).map((line, i) => {
    const email = line.match(/[\w.+-]+@[\w-]+\.[\w.]+/);
    return <li key={i}>{email ? <a href={`mailto:${email[0]}`}>{line}</a> : line}</li>;
});

export default function Home({ settings: s, markets, team, formToken }: Props) {
    const [menuOpen, setMenuOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);
    const { flash } = usePage<any>().props as { flash?: { success?: string } };
    const form = useForm({ name: '', email: '', phone: '', company: '', subject: '', message: '', consent: true, website: '', form_token: formToken });
    const formErrors = form.errors as Record<string, string>;

    // The server issues a fresh anti-spam token on every render (e.g. after an expired-form error).
    useEffect(() => { form.setData('form_token', formToken); }, [formToken]);

    useEffect(() => {
        const close = (event: KeyboardEvent) => event.key === 'Escape' && setMenuOpen(false);
        window.addEventListener('keydown', close);
        document.body.style.overflow = menuOpen ? 'hidden' : '';
        return () => { window.removeEventListener('keydown', close); document.body.style.overflow = ''; };
    }, [menuOpen]);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 40);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    // Fade sections in as they enter the viewport.
    useEffect(() => {
        const items = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window)) { items.forEach(el => el.classList.add('visible')); return; }
        const observer = new IntersectionObserver(entries => entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add('visible'); observer.unobserve(entry.target); }
        }), { threshold: 0.12 });
        items.forEach(el => observer.observe(el));
        return () => observer.disconnect();
    }, []);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('contact.store'), { preserveScroll: true, onSuccess: () => form.reset('name', 'email', 'phone', 'company', 'subject', 'message') });
    };

    const nav: [string, string][] = [['Home', '#home'], ['About', '#about'], ['Markets', '#markets'], ['Team', '#team'], ['Contact', '#contact']];
    const fields: [Field, string, string, boolean][] = [['name', 'Full name', 'text', true], ['email', 'Email address', 'email', true], ['phone', 'Phone', 'tel', false], ['company', 'Company', 'text', false]];
    const stats: [string, string][] = [['2013', 'Established in Dubai'], ['20+', 'Years of partner experience'], [String(markets.length || 5), 'Institution types served']];

    const input = (name: Field, label: string, type: string, required: boolean, className = '') => <label key={name} className={className}>
        <span>{label}{required && <em aria-hidden="true"> *</em>}</span>
        <input type={type} name={name} value={form.data[name]} required={required} onChange={e => form.setData(name, e.target.value)}
            aria-invalid={!!form.errors[name]} aria-describedby={form.errors[name] ? `${name}-error` : undefined} />
        {form.errors[name] && <small id={`${name}-error`}>{form.errors[name]}</small>}
    </label>;

    return <div className="public-site">
        <Head title="Executive Search for Emirati Talent">
            <meta name="description" content={s.hero_subtitle} />
            <meta property="og:title" content={s.site_name || 'SAF Partners'} />
            <link rel="canonical" href={window.location.origin} />
        </Head>
        <a href="#about" className="skip-link">Skip to content</a>

        <nav className={`site-nav ${scrolled || menuOpen ? 'scrolled' : ''}`} aria-label="Main navigation">
            <div className="nav-shell">
                <a href="#home" className="nav-logo" aria-label="SAF Partners home"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></a>
                <button className={`menu-button ${menuOpen ? 'open' : ''}`} onClick={() => setMenuOpen(!menuOpen)} aria-expanded={menuOpen} aria-controls="site-menu">
                    <span /><span /><span /><span className="sr-only">Menu</span>
                </button>
                <div id="site-menu" className={`nav-links ${menuOpen ? 'open' : ''}`}>
                    {nav.map(([label, href]) => <a key={label} href={href} onClick={() => setMenuOpen(false)}>{label}</a>)}
                </div>
            </div>
        </nav>

        <header id="home" className="hero">
            <div className="hero-copy">
                <h1><img src="/images/saf-partners-logo.png" alt={s.hero_title || 'SAF Partners'} className="hero-logo" /></h1>
                <p>{s.hero_subtitle}</p>
                <div className="hero-actions">
                    <a href="#contact" className="btn btn-primary">Get in touch</a>
                </div>
            </div>
            <a href="#about" className="scroll-cue" aria-label="Scroll to About us"><span /></a>
        </header>

        <main>
            <section id="about" className="about-section">
                <div className="about-card reveal">
                    <SectionTitle eyebrow="Boutique executive search">{s.about_heading || 'ABOUT US'}</SectionTitle>
                    <div className="about-grid">
                        {[s.about_1, s.about_2, s.about_3].map((text, i) => <article key={i}>
                            <div className="about-icon" aria-hidden="true">{icons[i]}</div>
                            <h3>{aboutLabels[i]}</h3>
                            <p>{text}</p>
                        </article>)}
                    </div>
                </div>
            </section>

            <section id="markets" className="markets-section">
                <div className="container reveal">
                    <SectionTitle eyebrow="Where we search">{s.markets_heading || 'MARKETS'}</SectionTitle>
                    <p className="section-intro">{s.markets_intro}</p>
                    <div className="market-grid">
                        {markets.map((market) => <article className="market-item" key={market.id}>
                            <span className="market-circle"><img src={market.image || market.icon} alt="" loading="lazy" /></span>
                            <h3>{market.title}</h3>
                            {market.description && <p>{market.description}</p>}
                        </article>)}
                    </div>
                    {s.markets_footer && <div className="market-footer">{s.markets_footer.split('\n').map((line, i) => <p key={i}>{line}</p>)}</div>}
                </div>
            </section>

            <section id="team" className="team-section">
                <div className="container reveal">
                    <SectionTitle eyebrow="Who you work with">{s.team_heading || 'TEAM'}</SectionTitle>
                    <div className="team-copy">
                        <p className="lead">{s.team_text_1}</p>
                        <p>{s.team_text_2}</p>
                    </div>
                    <dl className="stats">
                        {stats.map(([value, label]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}
                    </dl>
                    {team.length > 0 && <div className="team-grid">{team.map(member => <article key={member.id}>
                        {member.photo && <img src={member.photo} alt={member.name} loading="lazy" />}
                        <div><h3>{member.name}</h3><strong>{member.position}</strong>{member.biography && <p>{member.biography}</p>}</div>
                    </article>)}</div>}
                </div>
            </section>

            <section id="contact" className="contact-section">
                <div className="container">
                    <div className="contact-heading reveal">
                        <SectionTitle eyebrow="Confidential enquiries">{s.contact_heading || 'CONTACT US'}</SectionTitle>
                        <p>{s.contact_intro}</p>
                    </div>
                    <div className="contact-grid reveal">
                        <aside className="contact-address">
                            <h3>Our office</h3>
                            <ul>{addressLines(s.contact_address)}</ul>
                        </aside>
                        <form onSubmit={submit} noValidate aria-label="Contact form">
                            {flash?.success && <div className="success-message" role="status">{flash.success}</div>}
                            <input className="honeypot" name="website" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={e => form.setData('website', e.target.value)} aria-hidden="true" />
                            <div className="form-row">{fields.map(([name, label, type, required]) => input(name, label, type, required))}</div>
                            {input('subject', 'Subject', 'text', false, 'full')}
                            <label className="full">
                                <span>Message</span>
                                <textarea rows={5} name="message" value={form.data.message} onChange={e => form.setData('message', e.target.value)}
                                    aria-invalid={!!form.errors.message} aria-describedby={form.errors.message ? 'message-error' : undefined} />
                                {form.errors.message && <small id="message-error">{form.errors.message}</small>}
                            </label>
                            <label className="consent">
                                <input type="checkbox" checked={form.data.consent} onChange={e => form.setData('consent', e.target.checked)} />
                                <span>I consent to SAF Partners using this information to respond to my enquiry, as described in the <Link href="/privacy-policy">Privacy Policy</Link>.</span>
                            </label>
                            {['consent', 'form_token', 'form'].map(key => formErrors[key] && <small key={key} className="form-error" role="alert">{formErrors[key]}</small>)}
                            <button type="submit" className="btn btn-primary" disabled={form.processing}>{form.processing ? 'Sending…' : 'Send message'}</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>

        <footer className="site-footer">
            <div className="container">
                <img src="/images/saf-partners-logo.png" alt="SAF Partners" />
                <span>© {new Date().getFullYear()} {s.footer_text}</span>
                <div>
                    <Link href="/privacy-policy">Privacy</Link>
                    <Link href="/terms">Terms</Link>
                    <a href="#home" className="to-top" aria-label="Back to top">↑</a>
                </div>
            </div>
        </footer>
    </div>;
}
