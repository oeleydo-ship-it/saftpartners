import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Item = Record<string, any> & { id: number };
type Props = { settings: Record<string,string>; markets: Item[]; team: Item[]; contacts: Item[]; media: Item[]; audit: Item[]; stats: Record<string,number> };
const contentFields = [
    ['hero_subtitle','Hero subtitle',false],['hero_cta_label','Hero CTA label',false],['hero_cta_url','Hero CTA URL',false],
    ['about_heading','About heading',false],['about_1','About column 1',true],['about_2','About column 2',true],['about_3','About column 3',true],
    ['markets_heading','Markets heading',false],['markets_intro','Markets introduction',true],['markets_footer','Markets footer',true],
    ['team_heading','Team heading',false],['team_text_1','Team paragraph 1',true],['team_text_2','Team paragraph 2',true],
    ['contact_heading','Contact heading',false],['contact_intro','Contact introduction',true],['contact_address','Contact address',true],
    ['footer_text','Footer text',false],
] as const;
const legalDocs = [['privacy_policy','Privacy Policy','/privacy-policy'],['terms','Terms of Use','/terms']] as const;

function MarketRow({ item }: { item: Item }) {
    const form = useForm({ title:item.title, slug:item.slug, description:item.description||'', image:item.image||'', icon:item.icon||'', sort_order:item.sort_order, is_active:!!item.is_active });
    return <tr><td><input value={form.data.title} onChange={e=>form.setData('title',e.target.value)} /></td><td><input value={form.data.image} onChange={e=>form.setData('image',e.target.value)} /></td><td><input type="number" value={form.data.sort_order} onChange={e=>form.setData('sort_order',+e.target.value)} /></td><td><input type="checkbox" checked={form.data.is_active} onChange={e=>form.setData('is_active',e.target.checked)} /></td><td className="admin-actions"><button onClick={()=>form.put(route('admin.markets.update',item.id),{preserveScroll:true})}>Save</button><button className="danger" onClick={()=>confirm('Archive this market?')&&router.delete(route('admin.markets.destroy',item.id),{preserveScroll:true})}>Archive</button></td></tr>;
}

function TeamRow({ item }: { item: Item }) {
    const form = useForm({ name:item.name, slug:item.slug, position:item.position, biography:item.biography||'', photo:item.photo||'', email:item.email||'', linkedin_url:item.linkedin_url||'', sort_order:item.sort_order, is_active:!!item.is_active });
    return <tr><td><input value={form.data.name} onChange={e=>form.setData('name',e.target.value)} /></td><td><input value={form.data.position} onChange={e=>form.setData('position',e.target.value)} /></td><td><input value={form.data.photo} onChange={e=>form.setData('photo',e.target.value)} placeholder="/storage/media/..." /></td><td><input type="number" value={form.data.sort_order} onChange={e=>form.setData('sort_order',+e.target.value)} /></td><td><input type="checkbox" checked={form.data.is_active} onChange={e=>form.setData('is_active',e.target.checked)} /></td><td className="admin-actions"><button onClick={()=>form.put(route('admin.team.update',item.id),{preserveScroll:true})}>Save</button><button className="danger" onClick={()=>confirm('Archive this team member?')&&router.delete(route('admin.team.destroy',item.id),{preserveScroll:true})}>Archive</button></td></tr>;
}

export default function Dashboard(props: Props) {
    const [tab,setTab]=useState('overview');
    const { auth, flash }=usePage<any>().props;
    const content=useForm({settings:{...props.settings}});
    const market=useForm({title:'',slug:'',description:'',image:'',icon:'',sort_order:props.markets.length,is_active:true});
    const team=useForm({name:'',slug:'',position:'',biography:'',photo:'',email:'',linkedin_url:'',sort_order:props.team.length,is_active:true});
    const media=useForm<{file:File|null;alt_text:string}>({file:null,alt_text:''});
    const sections=['overview','content','legal','markets','team','contacts','media','audit'];
    const setSetting=(key:string,value:string)=>content.setData('settings',{...content.data.settings,[key]:value});
    const saveSettings=(e:FormEvent)=>{e.preventDefault();content.put(route('admin.settings'),{preserveScroll:true});};
    const submitMedia=(e:FormEvent)=>{e.preventDefault();media.post(route('admin.media.store'),{forceFormData:true,preserveScroll:true,onSuccess:()=>media.reset()});};
    return <div className="admin-shell"><Head title="CMS Dashboard" />
        <aside className="admin-sidebar"><Link href="/"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></Link><nav>{sections.map(v=><button key={v} className={tab===v?'active':''} onClick={()=>setTab(v)}>{v[0].toUpperCase()+v.slice(1)}</button>)}</nav></aside>
        <main className="admin-main"><header className="admin-top"><div><h1>Content management</h1><small>Signed in as {auth.user.name} · {auth.user.role}</small></div><Link as="button" method="post" href={route('logout')}>Log out</Link></header>
        {flash?.success&&<div className="admin-success">{flash.success}</div>}
        {tab==='overview'&&<><div className="stat-grid">{Object.entries(props.stats).map(([label,value])=><div className="stat-card" key={label}><span>{label.replace(/([A-Z])/g,' $1')}</span><strong>{value}</strong></div>)}</div><section className="admin-panel"><h2>Quick start</h2><p>Use Content to edit every public section. Markets and Team are database-backed collections; Media provides securely validated uploads, and Contacts stores all website enquiries.</p><a href="/" target="_blank" rel="noreferrer" className="admin-primary">Preview website</a></section></>}
        {tab==='content'&&<section className="admin-panel"><h2>Public website content</h2><form className="admin-form-grid" onSubmit={e=>{e.preventDefault();content.put(route('admin.settings'),{preserveScroll:true});}}>{contentFields.map(([key,label,long])=><label className={long?'wide':''} key={key}>{label}{long?<textarea rows={4} value={content.data.settings[key]||''} onChange={e=>content.setData('settings',{...content.data.settings,[key]:e.target.value})}/>:<input value={content.data.settings[key]||''} onChange={e=>content.setData('settings',{...content.data.settings,[key]:e.target.value})}/>}</label>)}<div><button className="admin-primary" disabled={content.processing}>Save website content</button></div></form></section>}
        {tab==='legal'&&<section className="admin-panel"><h2>Privacy Policy &amp; Terms of Use</h2>
            <p className="admin-help">Formatting: start a line with <code>## </code> for a heading or <code>### </code> for a subheading, start lines with <code>- </code> for bullet points, and wrap words in <code>**double asterisks**</code> for bold. Leave a blank line between paragraphs. Email addresses become links automatically. Update the date whenever the wording changes.</p>
            <form onSubmit={saveSettings}>
                {legalDocs.map(([key,label,path])=><div className="legal-editor" key={key}>
                    <div className="legal-editor-head"><h3>{label}</h3><a href={path} target="_blank" rel="noreferrer">View page ↗</a></div>
                    <label>Last updated<input type="date" value={content.data.settings[`${key}_updated`]||''} onChange={e=>setSetting(`${key}_updated`,e.target.value)} /></label>
                    <label>Content<textarea value={content.data.settings[key]||''} onChange={e=>setSetting(key,e.target.value)} aria-invalid={!!(content.errors as Record<string,string>)[`settings.${key}`]} /></label>
                    {(content.errors as Record<string,string>)[`settings.${key}`]&&<small className="form-error">{(content.errors as Record<string,string>)[`settings.${key}`]}</small>}
                </div>)}
                <button className="admin-primary" disabled={content.processing}>{content.processing?'Saving…':'Save legal pages'}</button>
            </form>
        </section>}
        {tab==='markets'&&<><section className="admin-panel"><h2>Add market</h2><form className="admin-form-grid" onSubmit={e=>{e.preventDefault();market.post(route('admin.markets.store'),{preserveScroll:true,onSuccess:()=>market.reset()})}}><label>Title<input value={market.data.title} onChange={e=>{market.setData('title',e.target.value);market.setData('slug',e.target.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''))}} /></label><label>Slug<input value={market.data.slug} onChange={e=>market.setData('slug',e.target.value)} /></label><label className="wide">Image URL<input value={market.data.image} onChange={e=>market.setData('image',e.target.value)} /></label><button className="admin-primary">Add market</button></form></section><section className="admin-panel"><table className="admin-table"><thead><tr><th>Title</th><th>Image</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead><tbody>{props.markets.filter(v=>!v.deleted_at).map(v=><MarketRow item={v} key={v.id}/>)}</tbody></table></section></>}
        {tab==='team'&&<><section className="admin-panel"><h2>Add team member</h2><form className="admin-form-grid" onSubmit={e=>{e.preventDefault();team.post(route('admin.team.store'),{preserveScroll:true,onSuccess:()=>team.reset()})}}><label>Name<input value={team.data.name} onChange={e=>{team.setData('name',e.target.value);team.setData('slug',e.target.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''))}} /></label><label>Position<input value={team.data.position} onChange={e=>team.setData('position',e.target.value)} /></label><label className="wide">Biography<textarea value={team.data.biography} onChange={e=>team.setData('biography',e.target.value)} /></label><button className="admin-primary">Add member</button></form></section><section className="admin-panel"><table className="admin-table"><thead><tr><th>Name</th><th>Position</th><th>Photo</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead><tbody>{props.team.filter(v=>!v.deleted_at).map(v=><TeamRow item={v} key={v.id}/>)}</tbody></table></section></>}
        {tab==='contacts'&&<section className="admin-panel"><h2>Contact submissions</h2><table className="admin-table"><thead><tr><th>Date</th><th>From</th><th>Subject / message</th><th>Status</th></tr></thead><tbody>{props.contacts.map(c=><tr key={c.id}><td>{new Date(c.created_at).toLocaleDateString()}</td><td><strong>{c.name}</strong><br/>{c.email}<br/>{c.company}</td><td><strong>{c.subject}</strong><br/>{c.message}</td><td><select value={c.status} onChange={e=>router.patch(route('admin.contacts.status',[c.id,e.target.value]),{}, {preserveScroll:true})}>{['new','read','replied','archived','spam'].map(v=><option key={v}>{v}</option>)}</select></td></tr>)}</tbody></table></section>}
        {tab==='media'&&<><section className="admin-panel"><h2>Secure media upload</h2><form className="admin-form-grid" onSubmit={submitMedia}><label>Select JPG, PNG, WEBP or PDF (max 8 MB)<input type="file" accept="image/jpeg,image/png,image/webp,application/pdf" onChange={e=>media.setData('file',e.target.files?.[0]||null)} /></label><label>Alt text<input value={media.data.alt_text} onChange={e=>media.setData('alt_text',e.target.value)} /></label><button className="admin-primary">Upload</button></form></section><section className="admin-panel"><div className="media-grid">{props.media.map(m=><div className="media-card" key={m.id}>{m.mime_type.startsWith('image/')?<img src={m.url} alt={m.alt_text||''}/>:<div>PDF</div>}<div>{m.original_name}<br/><button onClick={()=>navigator.clipboard.writeText(m.url)}>Copy URL</button> <button onClick={()=>confirm('Delete this file?')&&router.delete(route('admin.media.destroy',m.id),{preserveScroll:true})}>Delete</button></div></div>)}</div></section></>}
        {tab==='audit'&&<section className="admin-panel"><h2>Audit log</h2><table className="admin-table"><thead><tr><th>Date</th><th>Action</th><th>Description</th><th>IP</th></tr></thead><tbody>{props.audit.map(a=><tr key={a.id}><td>{new Date(a.created_at).toLocaleString()}</td><td>{a.action}</td><td>{a.description}</td><td>{a.ip_address}</td></tr>)}</tbody></table></section>}
        </main></div>;
}
