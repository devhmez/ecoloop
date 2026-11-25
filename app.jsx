/* EcoLoop - Pickups & Vehicles (source JSX) */
const { useState, useEffect, useMemo, useRef, useCallback, createContext, useContext } = React;
const cx = (...a) => a.filter(Boolean).join(' ');
const fmt = (n, d = 0) => Number(n).toLocaleString('en-US', { maximumFractionDigits: d, minimumFractionDigits: d });
const ICONS = {
  leaf: ['M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z', 'M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12'],
  home: ['m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'M9 22V12h6v10'],
  grid: ['M3 3h7v9H3z', 'M14 3h7v5h-7z', 'M14 12h7v9h-7z', 'M3 16h7v5H3z'],
  scan: ['M3 7V5a2 2 0 0 1 2-2h2', 'M17 3h2a2 2 0 0 1 2 2v2', 'M21 17v2a2 2 0 0 1-2 2h-2', 'M7 21H5a2 2 0 0 1-2-2v-2', 'M7 12h10'],
  flag: ['M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z', 'M4 22v-7'],
  truck: ['M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2', 'M15 18H9', 'M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14', 'M5 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0', 'M15 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0'],
  pin: ['M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z', 'M9 10a3 3 0 1 0 6 0 3 3 0 1 0-6 0'],
  gift: ['M20 12v10H4V12', 'M2 7h20v5H2z', 'M12 22V7', 'M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z', 'M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z'],
  chart: ['M3 3v18h18', 'M18 17V9', 'M13 17V5', 'M8 17v-3'],
  sliders: ['M4 21v-7', 'M4 10V3', 'M12 21v-9', 'M12 8V3', 'M20 21v-5', 'M20 12V3', 'M1 14h6', 'M9 8h6', 'M17 16h6'],
  search: ['M3 11a8 8 0 1 0 16 0 8 8 0 1 0-16 0', 'm21 21-4.3-4.3'],
  bell: ['M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9', 'M10.3 21a1.94 1.94 0 0 0 3.4 0'],
  menu: ['M4 6h16', 'M4 12h16', 'M4 18h16'],
  x: ['M18 6 6 18', 'm6 6 12 12'],
  check: ['M20 6 9 17l-5-5'],
  upload: ['M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4', 'M17 8l-5-5-5 5', 'M12 3v12'],
  sparkles: ['M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z', 'M19 16v4', 'M17 18h4'],
  users: ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M5 7a4 4 0 1 0 8 0 4 4 0 1 0-8 0', 'M22 21v-2a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75'],
  user: ['M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2', 'M8 7a4 4 0 1 0 8 0 4 4 0 1 0-8 0'],
  award: ['M7 9a5 5 0 1 0 10 0 5 5 0 1 0-10 0', 'M8.21 13.89 7 23l5-3 5 3-1.21-9.12'],
  droplet: ['M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z'],
  zap: ['M13 2 3 14h9l-1 8 10-12h-9l1-8z'],
  arrowRight: ['M5 12h14', 'm12 5 7 7-7 7'],
  arrowUpRight: ['M7 17 17 7', 'M7 7h10v10'],
  clock: ['M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0', 'M12 6v6l4 2'],
  mail: ['M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z', 'm22 7-10 7L2 7'],
  phone: ['M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z'],
  bottle: ['M10 2h4', 'M10 2v3l-2 3v12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V8l-2-3V2', 'M8 13h8'],
  file: ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'M14 2v6h6', 'M8 13h8', 'M8 17h8'],
  glass: ['M8 22h8', 'M12 15v7', 'M7 2h10l-1 8a4 4 0 0 1-8 0z'],
  can: ['M7 3h10l1 3v12l-1 3H7l-1-3V6z', 'M6 6h12', 'M6 18h12'],
  laptop: ['M4 5h16v11H4z', 'M2 20h20'],
  sun: ['M8 12a4 4 0 1 0 8 0 4 4 0 1 0-8 0', 'M12 2v2', 'M12 20v2', 'm4.9 4.9 1.4 1.4', 'm17.7 17.7 1.4 1.4', 'M2 12h2', 'M20 12h2', 'm4.9 19.1 1.4-1.4', 'm17.7 6.3 1.4-1.4'],
  moon: ['M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z'],
  info: ['M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0', 'M12 16v-4', 'M12 8h.01'],
  plus: ['M5 12h14', 'M12 5v14'],
  eye: ['M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z', 'M9 12a3 3 0 1 0 6 0 3 3 0 1 0-6 0'],
  logout: ['M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4', 'm16 17 5-5-5-5', 'M21 12H9'],
  building: ['M3 21h18', 'M5 21V7l7-4 7 4v14', 'M9 9h1', 'M9 13h1', 'M14 9h1', 'M14 13h1', 'M10 21v-4h4v4'],
  calendar: ['M8 2v4', 'M16 2v4', 'M3 6h18v15H3z', 'M3 10h18'],
  trend: ['m22 7-8.5 8.5-5-5L2 17', 'M16 7h6v6'],
  star: ['m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z'],
  shield: ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'm9 12 2 2 4-4'],
  loop: ['M3 12a9 9 0 0 1 15-6.7L21 8', 'M21 3v5h-5', 'M21 12a9 9 0 0 1-15 6.7L3 16', 'M8 16H3v5'],
  chevron: ['m6 9 6 6 6-6'],
  filter: ['M22 3H2l8 9.46V19l4 2v-8.54z'],
  cpu: ['M6 6h12v12H6z', 'M9 9h6v6H9z', 'M9 2v4', 'M15 2v4', 'M9 18v4', 'M15 18v4', 'M2 9h4', 'M2 15h4', 'M18 9h4', 'M18 15h4'],
  recycle: ['M7 19H4.8a1.8 1.8 0 0 1-1.6-2.6L5 13', 'm11 19 2 2-2 2', 'M8 8 5.5 4.5a1.8 1.8 0 0 1 3 0L10 7', 'M14 5.4l2.5-.9', 'M17 13l2.2 3.4a1.8 1.8 0 0 1-1.5 2.6H13']
};
function Icon({ n, s = 20, sw = 1.8, className, style }) {
  const d = ICONS[n] || [];
  return (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round" className={className} style={style} aria-hidden="true" focusable="false">
      {d.map((p, i) => <path key={i} d={p} />)}
    </svg>
  );
}
const CATS = {
  plastic: { label: 'Plastic', icon: 'bottle', color: 'var(--c-plastic)' },
  paper: { label: 'Paper', icon: 'file', color: 'var(--c-paper)' },
  glass: { label: 'Glass', icon: 'glass', color: 'var(--c-glass)' },
  metal: { label: 'Metal', icon: 'can', color: 'var(--c-metal)' },
  organic: { label: 'Organic', icon: 'leaf', color: 'var(--c-organic)' },
  electronics: { label: 'Electronics', icon: 'laptop', color: 'var(--c-electronics)' }
};
const CAT_KEYS = Object.keys(CATS);
const SEED_COLLECTIONS = [
  { id: 'C-1049', owner: 'You', date: 'Today · Mon 26 Oct', slot: '11:00 – 13:00', cats: ['plastic', 'metal'], status: 'On the way', collector: 'Hichem Mansouri', vehicle: 'Truck T-14', addr: '12 Rue Ibn Khaldoun, Ariana', x: 36, y: 38 },
  { id: 'C-1051', owner: 'You', date: 'Wed 28 Oct', slot: '09:00 – 11:00', cats: ['plastic', 'paper'], status: 'Scheduled', collector: 'Nour Gharbi', vehicle: 'Truck T-09', addr: '12 Rue Ibn Khaldoun, Ariana', x: 36, y: 38 },
  { id: 'C-1052', owner: 'You', date: 'Sat 31 Oct', slot: '14:00 – 16:00', cats: ['glass'], status: 'Scheduled', collector: 'Hichem Mansouri', vehicle: 'Truck T-14', addr: '12 Rue Ibn Khaldoun, Ariana', x: 36, y: 38 },
  { id: 'C-1040', owner: 'You', date: 'Mon 19 Oct', slot: '09:00 – 11:00', cats: ['paper'], status: 'Completed', collector: 'Nour Gharbi', vehicle: 'Truck T-09', addr: '12 Rue Ibn Khaldoun, Ariana', x: 36, y: 38 },
  { id: 'C-1034', owner: 'You', date: 'Fri 9 Oct', slot: '14:00 – 16:00', cats: ['plastic', 'glass'], status: 'Completed', collector: 'Hichem Mansouri', vehicle: 'Truck T-14', addr: '12 Rue Ibn Khaldoun, Ariana', x: 36, y: 38 },
  { id: 'C-1027', owner: 'You', date: 'Tue 29 Sep', slot: '09:00 – 11:00', cats: ['electronics'], status: 'Completed', collector: 'Amine Jlassi', vehicle: 'Van V-02', addr: '12 Rue Ibn Khaldoun, Ariana', x: 36, y: 38 },
  { id: 'C-1053', owner: 'Sana K.', date: 'Today · Mon 26 Oct', slot: '09:00 – 11:00', cats: ['organic'], status: 'On the way', collector: 'Amine Jlassi', vehicle: 'Van V-02', addr: 'Rue de Marseille, Lac 1', x: 72, y: 50 },
  { id: 'C-1054', owner: 'Mehdi B.', date: 'Tue 27 Oct', slot: '14:00 – 16:00', cats: ['paper', 'plastic'], status: 'Scheduled', collector: 'Unassigned', vehicle: '—', addr: 'Rue Habib Bourguiba, Ariana', x: 44, y: 24 },
  { id: 'C-1055', owner: 'Ines T.', date: 'Wed 28 Oct', slot: '09:00 – 11:00', cats: ['glass'], status: 'Scheduled', collector: 'Unassigned', vehicle: '—', addr: 'Avenue Taieb Mhiri, La Marsa', x: 84, y: 22 },
  { id: 'C-1048', owner: 'Karim A.', date: 'Sun 25 Oct', slot: '09:00 – 11:00', cats: ['metal'], status: 'Completed', collector: 'Nour Gharbi', vehicle: 'Truck T-09', addr: 'Rue de Carthage, El Menzah', x: 54, y: 66 }
];
const STATUS_KIND = { Pending: 'warn', 'In review': 'info', Assigned: 'info', Resolved: 'ok', Scheduled: 'info', 'On the way': 'warn', Completed: 'ok', Cancelled: 'bad', Active: 'ok', Suspended: 'bad', Open: 'ok', Closed: 'mute', Low: 'ok', Normal: 'info', High: 'bad' };
const Ctx = createContext(null);
const useApp = () => useContext(Ctx);
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
function Badge({ kind, children }) { return <span className={cx('badge', 'b-' + (kind || 'mute'))}><i />{children}</span>; }
function Status({ s }) { return <Badge kind={STATUS_KIND[s]}>{s}</Badge>; }
function CatBadge({ k, onClick, active, small }) {
  const c = CATS[k];
  const Tag = onClick ? 'button' : 'span';
  return (
    <Tag className={cx('cat', small && 'cat-sm', active && 'on', onClick && 'cat-btn')} style={{ '--c': c.color }} onClick={onClick} {...(onClick ? { type: 'button', 'aria-pressed': !!active } : {})}>
      <Icon n={c.icon} s={small ? 14 : 16} /> {c.label}
    </Tag>
  );
}
function Seg({ options, value, onChange, label }) {
  return (
    <div className="seg" role="tablist" aria-label={label}>
      {options.map(o => (
        <button key={o.v} role="tab" aria-selected={value === o.v} className={value === o.v ? 'on' : ''} onClick={() => onChange(o.v)}>{o.l}</button>
      ))}
    </div>
  );
}
function Modal({ open, onClose, title, children, wide, sub }) {
  useEffect(() => {
    if (!open) return;
    const h = e => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [open, onClose]);
  if (!open) return null;
  return (
    <div className="overlay" onMouseDown={e => { if (e.target === e.currentTarget) onClose(); }}>
      <div className={cx('modal', wide && 'wide')} role="dialog" aria-modal="true" aria-label={title}>
        <div className="modal-h">
          <div><h3>{title}</h3>{sub && <p className="muted sm">{sub}</p>}</div>
          <button className="icon-btn" onClick={onClose} aria-label="Close dialog"><Icon n="x" /></button>
        </div>
        <div className="modal-b">{children}</div>
      </div>
    </div>
  );
}
function Toasts() {
  const { toasts } = useApp();
  return (
    <div className="toasts" aria-live="polite">
      {toasts.map(t => (
        <div key={t.id} className={cx('toast', t.kind)}><Icon n={t.kind === 'bad' ? 'info' : 'check'} s={18} /><span>{t.msg}</span></div>
      ))}
    </div>
  );
}
function ThemeBtn() {
  const { theme, setTheme } = useApp();
  const dark = theme === 'dark' || (theme === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
  return <button className="icon-btn" onClick={() => setTheme(dark ? 'light' : 'dark')} aria-label={dark ? 'Switch to light theme' : 'Switch to dark theme'}><Icon n={dark ? 'sun' : 'moon'} /></button>;
}
const BLOCKS = [[20, 20, 90, 60], [125, 20, 95, 60], [235, 20, 100, 60], [350, 20, 80, 60], [445, 20, 135, 60], [20, 95, 90, 70], [125, 95, 95, 70], [235, 95, 100, 70], [350, 95, 80, 70], [445, 95, 135, 70], [20, 180, 90, 60], [125, 180, 95, 60], [235, 180, 100, 60], [350, 180, 80, 60], [445, 180, 135, 60], [20, 255, 90, 85], [125, 255, 95, 85], [235, 255, 100, 85], [350, 255, 80, 85]];
function CityMap({ markers = [], activeId, onSelect, onPick, pin, caption = 'Illustrative map. Positions are examples.' }) {
  const ref = useRef(null);
  const click = e => {
    if (!onPick) return;
    const r = ref.current.getBoundingClientRect();
    onPick({ x: clamp(((e.clientX - r.left) / r.width) * 100, 3, 97), y: clamp(((e.clientY - r.top) / r.height) * 100, 4, 96) });
  };
  const active = markers.find(m => m.id === activeId);
  return (
    <figure className="map">
      <svg ref={ref} viewBox="0 0 600 360" className="chart" role="img" aria-label="Illustrative city map" onClick={click} style={onPick ? { cursor: 'crosshair' } : null}>
        <rect width="600" height="360" rx="18" fill="var(--map-bg)" />
        {BLOCKS.map((b, i) => <rect key={i} x={b[0]} y={b[1]} width={b[2]} height={b[3]} rx="10" fill={i === 16 || i === 2 ? 'var(--map-park)' : 'var(--map-block)'} />)}
        <ellipse cx="530" cy="310" rx="78" ry="46" fill="var(--map-water)" />
        <path d="M0 330L600 20" stroke="var(--map-road)" strokeWidth="12" fill="none" />
        <path d="M0 330L600 20" stroke="var(--map-block)" strokeWidth="1.5" strokeDasharray="8 8" fill="none" />
        {markers.map(m => {
          const cx0 = (m.x / 100) * 600, cy0 = (m.y / 100) * 360, on = m.id === activeId;
          return (
            <g key={m.id} transform={`translate(${cx0} ${cy0})`} className={cx('mk', on && 'on')} tabIndex={0} role="button" aria-label={m.label} onClick={e => { e.stopPropagation(); onSelect && onSelect(m.id); }} onKeyDown={e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onSelect && onSelect(m.id); } }}>
              {on && <circle r="20" fill={m.color} opacity=".22"><animate attributeName="r" values="14;24;14" dur="2.2s" repeatCount="indefinite" /></circle>}
              <circle r={on ? 12 : 9} fill={m.color} stroke="#fff" strokeWidth="3" />
            </g>
          );
        })}
        {pin && <g transform={`translate(${(pin.x / 100) * 600} ${(pin.y / 100) * 360})`} pointerEvents="none"><path d="M0 0c-9-10-14-16-14-23a14 14 0 0 1 28 0c0 7-5 13-14 23z" fill="#C2413A" stroke="#fff" strokeWidth="2.5" /><circle cy="-23" r="5" fill="#fff" /></g>}
        {active && (() => {
          const px = clamp((active.x / 100) * 600, 70, 530), py = (active.y / 100) * 360;
          const below = py < 60;
          return <g transform={`translate(${px} ${below ? py + 20 : py - 44})`} pointerEvents="none"><rect x="-70" width="140" height="28" rx="14" fill="var(--forest)" /><text y="18" textAnchor="middle" fill="#fff" fontSize="12" fontWeight="600">{active.label.length > 22 ? active.label.slice(0, 21) + '…' : active.label}</text></g>;
        })()}
      </svg>
      <figcaption>{caption}</figcaption>
    </figure>
  );
}
function Field({ id, label, error, hint, children }) {
  return (
    <div className="field">
      <label htmlFor={id}>{label}</label>
      {children}
      {error ? <p className="err" role="alert">{error}</p> : hint ? <p className="hint">{hint}</p> : null}
    </div>
  );
}
const DATES = ['Tue 27 Oct', 'Wed 28 Oct', 'Thu 29 Oct', 'Fri 30 Oct', 'Sat 31 Oct', 'Mon 2 Nov'];
const SLOTS = ['09:00 – 11:00', '11:00 – 13:00', '14:00 – 16:00', '16:00 – 18:00'];
function PageHead({ title, sub, children }) {
  return <div className="ph"><div><h1>{title}</h1>{sub && <p className="muted">{sub}</p>}</div>{children && <div className="ph-act">{children}</div>}</div>;
}
function AHead({ title, sub, children }) { return <div className="a-head"><div><h1>{title}</h1>{sub && <p className="muted">{sub}</p>}</div>{children && <div className="ph-act">{children}</div>}</div>; }
const COLLECTORS = ['Unassigned', 'Hichem Mansouri', 'Nour Gharbi', 'Amine Jlassi'];
const NEXT_STATUS = { Scheduled: 'On the way', 'On the way': 'Completed' };
function Collections() {
  const { collections, setCollections, setModal, push, setPoints, addActivity } = useApp();
  const [tab, setTab] = useState('up');
  const mine = collections.filter(c => c.owner === 'You');
  const up = mine.filter(c => c.status === 'Scheduled' || c.status === 'On the way');
  const hist = mine.filter(c => c.status === 'Completed' || c.status === 'Cancelled');
  const list = tab === 'up' ? up : hist;
  const [sel, setSel] = useState(null);
  const active = sel || (up[0] && up[0].id);
  const advance = c => {
    const ns = NEXT_STATUS[c.status];
    setCollections(cs => cs.map(x => (x.id === c.id ? { ...x, status: ns } : x)));
    if (ns === 'Completed') { setPoints(p => p + 60); addActivity({ icon: 'truck', text: `Collection ${c.id} completed`, sub: c.cats.map(k => CATS[k].label).join(', '), pts: '+60' }); push(`${c.id} completed. +60 EcoPoints`); }
    else push(`${c.id} is now ${ns.toLowerCase()}`);
  };
  const cancel = c => { setCollections(cs => cs.map(x => (x.id === c.id ? { ...x, status: 'Cancelled' } : x))); push(`${c.id} cancelled`, 'bad'); };
  const markers = [];
  up.forEach(c => {
    markers.push({ id: c.id, x: c.status === 'On the way' ? c.x - 9 : c.x, y: c.status === 'On the way' ? c.y + 8 : c.y, color: c.status === 'On the way' ? '#F59E0B' : '#2D8C74', label: c.status === 'On the way' ? `Truck · ${c.id}` : `${c.id} · ${c.date.replace('Today · ', '')}` });
  });
  return (
    <>
      <PageHead title="My Collections" sub="Pickups at your address, with the collector and live status."><button className="btn btn-primary" onClick={() => setModal('pickup')}><Icon n="plus" s={18} /> Request Pickup</button></PageHead>
      <div className="grid two gap-top top-al">
        <div className="stack">
          <Seg label="Collections" value={tab} onChange={setTab} options={[{ v: 'up', l: `Upcoming (${up.length})` }, { v: 'hist', l: `History (${hist.length})` }]} />
          {list.length === 0 && <div className="card pad empty"><span className="empty-ic"><Icon n="truck" s={28} /></span><b>{tab === 'up' ? 'No upcoming pickups' : 'Nothing here yet'}</b><span className="muted">{tab === 'up' ? 'Request a pickup to see it here.' : 'Completed pickups appear here.'}</span></div>}
          {list.map(c => (
            <article key={c.id} className={cx('card pad col', active === c.id && 'sel')} onClick={() => setSel(c.id)}>
              <div className="col-top"><div><b>{c.date}</b><span className="muted"> · {c.slot}</span></div><Status s={c.status} /></div>
              <div className="chips">{c.cats.map(k => <CatBadge key={k} k={k} small />)}</div>
              <div className="col-who"><span className="av sm">{c.collector === 'Unassigned' ? '?' : c.collector.split(' ').map(w => w[0]).join('')}</span><div><b>{c.collector}</b><span className="muted sm">{c.vehicle} · {c.id}</span></div></div>
              {(c.status === 'Scheduled' || c.status === 'On the way') && (
                <div className="row" onClick={e => e.stopPropagation()}>
                  <button className="btn btn-sm btn-primary" onClick={() => advance(c)}>{c.status === 'Scheduled' ? 'Simulate: truck departs' : 'Simulate: mark collected'}</button>
                  <button className="btn btn-sm btn-ghost" onClick={() => cancel(c)}>Cancel</button>
                </div>
              )}
            </article>
          ))}
        </div>
        <div className="sticky-col"><div className="card pad"><div className="card-h"><h3>Live map</h3><span className="muted sm">Sample markers</span></div>
          <CityMap markers={markers} activeId={active} onSelect={setSel} />
          <div className="legend-row"><span><i style={{ background: '#2D8C74' }} /> Pickup address</span><span><i style={{ background: '#F59E0B' }} /> Truck on the way</span></div>
        </div></div>
      </div>
    </>
  );
}
function AdminCollections() {
  const { collections, setCollections, push } = useApp();
  const [f, setF] = useState('All');
  const list = collections.filter(c => f === 'All' || c.status === f);
  const upd = (id, patch, msg) => { setCollections(cs => cs.map(c => (c.id === id ? { ...c, ...patch } : c))); if (msg) push(msg); };
  return (
    <>
      <AHead title="Collections" sub="Assign collectors and track every pickup." />
      <div className="acard">
        <div className="acard-h"><h3>All pickups</h3><div className="fchips" role="group" aria-label="Filter by status">{['All', 'Scheduled', 'On the way', 'Completed', 'Cancelled'].map(s => <button key={s} className={cx('fchip', f === s && 'on')} onClick={() => setF(s)} aria-pressed={f === s}>{s}</button>)}</div></div>
        <div className="tbl-wrap"><table className="tbl dense"><thead><tr><th>ID</th><th>Citizen</th><th>When</th><th>Materials</th><th>Collector</th><th>Vehicle</th><th>Status</th><th /></tr></thead>
          <tbody>{list.map(c => (
            <tr key={c.id}><td><b>{c.id}</b></td><td>{c.owner}<div className="muted xs">{c.addr}</div></td><td>{c.date.replace('Today · ', 'Today ')}<div className="muted xs">{c.slot}</div></td>
              <td><div className="chips">{c.cats.map(k => <CatBadge key={k} k={k} small />)}</div></td>
              <td><select aria-label={`Collector for ${c.id}`} className="sel-s" value={c.collector} disabled={c.status === 'Completed' || c.status === 'Cancelled'} onChange={e => upd(c.id, { collector: e.target.value, vehicle: e.target.value === 'Unassigned' ? '—' : (c.vehicle !== '—' ? c.vehicle : 'Truck T-14') }, `${c.id} assigned to ${e.target.value}`)}>{COLLECTORS.map(n => <option key={n}>{n}</option>)}</select></td>
              <td><select aria-label={`Vehicle for ${c.id}`} className="sel-s" value={c.vehicle} disabled={c.collector === 'Unassigned' || c.status === 'Completed' || c.status === 'Cancelled'} onChange={e => upd(c.id, { vehicle: e.target.value }, `${c.id} now uses ${e.target.value}`)}>{FLEET.map(v => <option key={v}>{v}</option>)}</select></td>
              <td><Status s={c.status} /></td>
              <td>{NEXT_STATUS[c.status] ? <button className="btn btn-sm btn-ghost" onClick={() => upd(c.id, { status: NEXT_STATUS[c.status] }, `${c.id} is now ${NEXT_STATUS[c.status].toLowerCase()}`)}>→ {NEXT_STATUS[c.status]}</button> : null}</td></tr>
          ))}{list.length === 0 && <tr><td colSpan="8" className="muted center pad-s">Nothing in this state.</td></tr>}</tbody></table></div>
      </div>
    </>
  );
}

const FLEET = ['—', 'Truck T-09', 'Truck T-14', 'Van V-02'];

function Provider({ children }) {
  const [toasts, setToasts] = useState([]);
  const [points, setPoints] = useState(850);
  const [collections, setCollections] = useState(SEED_COLLECTIONS);
  const [modal, setModal] = useState(null);
  const [theme, setThemeState] = useState('system');
  useEffect(() => {
    const el = document.documentElement;
    if (theme === 'system') el.removeAttribute('data-theme'); else el.setAttribute('data-theme', theme);
  }, [theme]);
  const push = useCallback((msg, kind = 'ok') => {
    const id = Date.now() + Math.random();
    setToasts(t => [...t, { id, msg, kind }]);
    setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), 3800);
  }, []);
  const addActivity = useCallback(() => {}, []);
  const value = { toasts, push, points, setPoints, addActivity, collections, setCollections, modal, setModal, theme, setTheme: setThemeState };
  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
}

function PickupModal() {
  const { modal, setModal, collections, setCollections, push } = useApp();
  const [cats, setCats] = useState([]);
  const [date, setDate] = useState(DATES[0]);
  const [slot, setSlot] = useState(SLOTS[0]);
  const [addr, setAddr] = useState('12 Rue Ibn Khaldoun, Ariana');
  const [note, setNote] = useState('');
  const [err, setErr] = useState({});
  const toggle = k => setCats(c => (c.includes(k) ? c.filter(x => x !== k) : [...c, k]));
  const submit = e => {
    e.preventDefault();
    const er = {};
    if (!cats.length) er.cats = 'Choose at least one waste category.';
    if (addr.trim().length < 5) er.addr = 'Enter the pickup address.';
    setErr(er);
    if (Object.keys(er).length) return;
    const id = 'C-' + (1056 + collections.length);
    setCollections([{ id, owner: 'You', date, slot, cats, status: 'Scheduled', collector: 'Unassigned', vehicle: '—', addr: addr.trim(), x: 36, y: 38 }, ...collections]);
    push(`Pickup ${id} requested for ${date}`);
    setCats([]); setNote(''); setErr({}); setModal(null);
  };
  return (
    <Modal open={modal === 'pickup'} onClose={() => setModal(null)} title="Request a pickup" sub="Collectors are assigned the evening before. This is a demo, so nothing is sent.">
      <form onSubmit={submit} noValidate className="stack">
        <Field id="pk-cats" label="What are you putting out?" error={err.cats}>
          <div className="chips" id="pk-cats">{Object.keys(CATS).map(k => <CatBadge key={k} k={k} active={cats.includes(k)} onClick={() => toggle(k)} />)}</div>
        </Field>
        <div className="grid g2">
          <Field id="pk-date" label="Date"><select id="pk-date" value={date} onChange={e => setDate(e.target.value)}>{DATES.map(d => <option key={d}>{d}</option>)}</select></Field>
          <Field id="pk-slot" label="Time slot"><select id="pk-slot" value={slot} onChange={e => setSlot(e.target.value)}>{SLOTS.map(d => <option key={d}>{d}</option>)}</select></Field>
        </div>
        <Field id="pk-addr" label="Pickup address" error={err.addr}><input id="pk-addr" value={addr} onChange={e => setAddr(e.target.value)} /></Field>
        <Field id="pk-note" label="Note for the collector" hint="Optional"><textarea id="pk-note" rows="2" value={note} onChange={e => setNote(e.target.value)} placeholder="Bags are by the gate" /></Field>
        <div className="modal-f"><button type="button" className="btn btn-ghost" onClick={() => setModal(null)}>Cancel</button><button className="btn btn-primary" type="submit">Request pickup</button></div>
      </form>
    </Modal>
  );
}

function Root() {
  const [view, setView] = useState('citizen');
  return (
    <div className="main">
      <header className="top"><div className="top-in gut">
        <b style={{ fontFamily: 'var(--font-d)' }}>EcoLoop · Pickups &amp; Vehicles</b>
        <Seg label="View" value={view} onChange={setView} options={[{ v: 'citizen', l: 'Citizen: My Collections' }, { v: 'admin', l: 'Admin: Collections & vehicles' }]} />
        <div className="top-act"><ThemeBtn /></div>
      </div></header>
      <main className="content gut" id="main">{view === 'citizen' ? <Collections /> : <AdminCollections />}</main>
      <PickupModal />
      <Toasts />
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<Provider><Root /></Provider>);
