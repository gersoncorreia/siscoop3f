# Frontend Design Patterns (TALL Stack)

Implementation patterns and best practices for creating premium UIs with Blade, Livewire, and Alpine.js.

## Theme & Layout

### Glassmorphism Card (Blade)
```blade
<div class="relative overflow-hidden rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-8 shadow-xl">
    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-primary-500/20 blur-3xl"></div>
    <h2 class="relative text-2xl font-bold text-white">{{ $title }}</h2>
    <p class="relative mt-2 text-slate-300">{{ $slot }}</p>
</div>
```

### Gradients & Accents
```blade
<button class="relative group px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-indigo-600 hover:scale-105 transition-all duration-300">
    <span class="text-white font-semibold">Get Started</span>
    <div class="absolute inset-0 rounded-xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
</button>
```

## Interactivity (Alpine.js + Livewire)

### Reactive Panel
```blade
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="btn-primary">
        Toggle Panel
    </button>
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="mt-4 rounded-lg bg-white p-6 shadow-lg border border-slate-100">
        {{ $slot }}
    </div>
</div>
```

### Loading States (Livewire)
```blade
<button wire:click="save" class="btn-primary">
    <span wire:loading.remove>Save Changes</span>
    <span wire:loading>
        <x-icon name="spinner" class="animate-spin h-5 w-5" />
        Processing...
    </span>
</button>
```

## TailwindCSS Best Practices

- **Custom Tokens:** Use custom colors and fonts in `tailwind.config.js`.
- **Dark Mode:** Use `dark:` variants for all components.
- **Motion:** Use `transition`, `duration`, and `ease` classes liberally.
- **Asymmetry:** Use grid-breaking layouts and negative margins for character.
