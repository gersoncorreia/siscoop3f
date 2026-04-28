---
name: frontend-responsive-ui
description: Build responsive, mobile-first layouts using fluid containers, flexible units, and touch-friendly design. Use this skill when creating or modifying UI layouts, responsive grids, mobile navigation, or any interface that needs to adapt to different screen sizes. Apply when working with TailwindCSS responsive classes, media queries, and mobile-first styling.
---

# Frontend Responsive UI (Optimized)

**Rule:** Mobile-first development with fluid layouts, relative units, and touch-friendly targets.

## Core Directives

- **Mobile-First Mandatory:** Start with mobile styles, then enhance with `sm:`, `md:`, `lg:`, `xl:`.
- **Fluid Layouts:** Use `w-full` + `max-w-*` and `flex-1` instead of fixed pixel widths.
- **Relative Units:** Use `rem` (default in Tailwind) for spacing and typography. Use `vh`/`vw` for viewport-aware sizing.
- **Touch Friendly:** Minimum touch target size: 44x44px (iOS) / 48x48px (Android).
- **Typography:** Body text min 16px (1rem). Line length 45-75 chars (`max-w-prose`).

## Standard Breakpoints (Tailwind)

| Prefix | Value  | Device Class     |
|--------|--------|------------------|
| default| <640px | Mobile           |
| `sm:`  | 640px  | Small tablets    |
| `md:`  | 768px  | Tablets          |
| `lg:`  | 1024px | Laptops          |
| `xl:`  | 1280px | Desktops         |
| `2xl:` | 1536px | Large desktops   |

## Verification Checklist

- [ ] Started with mobile layout logic
- [ ] No horizontal scrolling on mobile (375px)
- [ ] No fixed pixel widths for large containers
- [ ] Touch targets are at least 44x44px
- [ ] Typography is readable without zoom
- [ ] Images have `srcset` or `loading="lazy"`
- [ ] Content priority: Secondary content hidden/collapsed on mobile

## Patterns & Examples

For detailed code patterns (Grids, Navs, Sidebars, Image Optimization), refer to:
- `references/responsive-patterns.md` - Blade + Tailwind code examples