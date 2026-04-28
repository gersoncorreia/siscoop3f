---
name: frontend-design
description: Create distinctive, production-grade frontend interfaces with high design quality. Use this skill when building web components, pages, or styling/beautifying any web UI. Primary stack: Blade + Livewire + Alpine.js + TailwindCSS (TALL Stack).
---

# Frontend Design (Optimized)

**Goal:** Create distinctive, production-grade interfaces that avoid generic "AI aesthetics."

## Core Principles

- **Bolder Aesthetic:** Commit to a clear direction (minimalism, maximalism, etc.). Avoid middle-of-the-road "standard" UI.
- **Typography Priority:** Pair distinctive display fonts with refined body fonts.
- **Visual Depth:** Use gradients, mesh backgrounds, subtle noise, and layered transparencies.
- **Micro-Interactions:** Use Alpine.js and Tailwind transitions for refined motion.
- **Asymmetry:** Use grid utilities creatively to avoid "boxed-in" feeling.

## TALL Stack Directives

- **Blade First:** Use anonymous components for UI primitives.
- **Livewire Focus:** Use for real-time interactivity and server-side state.
- **Alpine.js:** Use for client-side toggles, transitions, and local UI state.
- **TailwindCSS:** Prefer utility classes in templates. Define custom colors in `tailwind.config.js`.

## Verification Checklist

- [ ] Theme is cohesive (defined color palette, typography)
- [ ] No generic AI font pairings (Inter/Roboto default)
- [ ] Motion is applied to key interactions (hover, open, reveals)
- [ ] Accessibility: Sufficient color contrast and ARIA labels
- [ ] Loading & Empty states are designed
- [ ] Mobile-first responsive checks passed

## Patterns & Examples

For detailed code patterns (Glassmorphism, Animations, Loading states), refer to:
- `references/design-patterns.md` - Blade + Livewire code examples
- `references/responsive-patterns.md` - (Shared) Layout patterns