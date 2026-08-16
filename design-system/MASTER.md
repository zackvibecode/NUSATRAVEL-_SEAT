# SeatWeb Design System — Master

## Context
Internal travel operations dashboard (staff-only). Data-dense, information-first.

## Colors (existing, kekalkan)
- Brand: `#e4002b` (primary CTA)
- Brand hover: `#c40027`
- Brand soft: `#ffe9ed` (light bg)
- Fog: `#faf7f7` (canvas)
- Ink: `#14100f` (primary text)
- Charcoal: `#454245` (secondary text)
- Line: `#e8e4e2` (borders)
- Positive: `#1e7d46` + `#e5f5ec` (soft)
- Warning: `#a35b00` + `#fff4e0` (soft)
- Destructive: `#dc2626` (red-600, for errors)

## Typography
- Font: Inter (existing)
- Page title: `text-2xl sm:text-3xl font-black tracking-tight`
- Section title: `text-base sm:text-lg font-bold tracking-tight`
- Body: `text-sm`
- Meta/labels: `text-xs font-semibold text-charcoal uppercase tracking-wider`

## Spacing & Layout
- Page padding: `px-4 md:px-8 py-6 md:py-8`
- Section gap: `gap-4` (16px)
- Card padding: `p-4 sm:p-6` (compact, not p-8)
- Card radius: `rounded-xl` (12px)
- Grid: 12-col implicit; KPI row = 4 cols desktop, 2 cols mobile
- Max width: `max-w-6xl mx-auto`

## Components

### KPI Card
- White bg, `rounded-xl border border-line shadow-sm`
- Icon chip: `w-9 h-9 rounded-xl bg-brand-soft` (or positive/warning-soft)
- Value: `text-2xl font-black`
- Label: `text-xs font-semibold text-charcoal uppercase`
- Sub-info: `text-xs text-charcoal/70` (delta, trend, hint)

### Data Table
- Wrapper: `overflow-x-auto` for mobile
- Header: `text-xs font-semibold uppercase text-charcoal`, `bg-fog/40`
- Row: `divide-y divide-line`, hover `bg-fog/60`
- Cell padding: `px-4 py-3` (compact, not p-6)
- Status badges: `rounded-full px-2.5 py-1 text-xs font-bold`

### Chart (dashboard)
- Type: SVG line/area chart (no JS lib) — 6-month trend
- Series: revenue (line, brand) + pax (bars, charcoal/line)
- Direct labels on data points, never rely on hue alone
- A11y: `<svg role="img">` + `<title>` + `<desc>` + data table fallback
- Reduced motion: no animated transitions on chart

### Button
- Primary: `bg-brand text-white rounded-full px-6 py-3 font-bold`
- Secondary: `bg-fog text-ink rounded-full`
- Danger: `bg-red-50 text-red-600 border border-red-200`
- Focus: visible 2px brand outline (already in app.css)

### Empty State
- Icon chip `w-12 h-12 rounded-2xl bg-brand-soft` + `text-sm font-semibold` + `text-xs text-charcoal` hint

## Anti-Patterns to Avoid
- No emojis as icons (use SVG)
- No wide tables without `overflow-x-auto`
- No color-only meaning (pair with text/icons)
- No 8+ items in a horizontal nav (use More drawer — already done)
- No marketing gradients (internal tool)
