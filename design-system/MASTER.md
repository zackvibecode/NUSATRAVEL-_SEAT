# SeatWeb Design System — Master

## Direction
**Bento Grid + Glassmorphism** — travel operations dashboard with premium, Apple-style aesthetics.

## Typography
- **Headings:** Playfair Display (serif) — `font-serif text-3xl sm:text-4xl font-bold tracking-tight`
- **Body:** Inter — `text-sm`
- **Meta/labels:** `text-xs font-semibold text-charcoal uppercase tracking-wider`

## Colors
- Brand: `#e4002b` (primary CTA)
- Brand hover: `#c40027`
- Brand soft: `#ffe9ed`
- Fog: `#faf7f7` (canvas with subtle mesh gradient)
- Ink: `#14100f`
- Charcoal: `#454245`
- Line: `#e8e4e2`
- Positive: `#1e7d46` + `#e5f5ec`
- Warning: `#a35b00` + `#fff4e0`

## Glass System
```css
.glass {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: 0 8px 32px rgba(20, 16, 15, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6);
}
.glass-strong {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 12px 40px rgba(20, 16, 15, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.7);
}
```

## Canvas
Body has subtle warm mesh gradient (`radial-gradient` brand red + soft red + fog) with `background-attachment: fixed` so glass cards have depth to blur against.

## Components

### Bento Grid (dashboard)
- Grid: `grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4`
- KPI cards: 1x1 each
- Chart: `col-span-2 lg:col-span-3`
- Hermes feed: `col-span-2 lg:col-span-1`
- Upcoming trips: `col-span-2 lg:col-span-3`
- Attention trips: `col-span-2 lg:col-span-1`

### Cards
- Glass effect: `glass rounded-2xl`
- Radius: `rounded-2xl` (16px) — bento standard
- Padding: `p-4 sm:p-5` (compact)

### Buttons
- Primary: `bg-brand text-white rounded-full px-6 py-3 font-bold shadow-lg`
- Secondary: `bg-white/60 text-ink rounded-full`
- Danger: `bg-red-50 text-red-600 border border-red-200`

### Sidebar
- Glass: `background: rgba(255,255,255,0.65); backdrop-filter: blur(15px); border-right: rgba(255,255,255,0.4)`
- Active nav: `bg-brand text-white shadow-md`
- Hover: `bg-white/70 text-brand`

### Login Page
- Full-screen mesh gradient (brand red + soft red + warning-soft glow orbs)
- Brand panel: translucent glass border-left
- Login card: `glass-strong rounded-3xl`
- Serif hero: "Travel seat management, beautifully clear." with italic accent

## Anti-Patterns
- No plain white cards (`bg-white` only) — always glass
- No box shadows without glass (glass has its own)
- No 8+ items in horizontal nav (More drawer)
- No marketing gradients (internal tool)
- No emojis as icons (SVG only)
