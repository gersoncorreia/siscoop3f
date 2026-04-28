# Responsive Design Patterns (Blade + TailwindCSS)

Detailed code patterns and implementation guides for responsive UI.

## Mobile-First Component Patterns

### Responsive Grid
```blade
{{-- mobile-first grid: 1 col on mobile, 2 on tablet, 4 on desktop --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($items as $item)
        <x-card :item="$item" />
    @endforeach
</div>
```

### Navigation (Blade + Alpine.js)
```blade
<nav x-data="{ open: false }" class="relative">
    {{-- Mobile menu button --}}
    <button @click="open = !open" class="lg:hidden" aria-label="Toggle menu">
        <x-icon name="menu" class="h-6 w-6" />
    </button>

    {{-- Desktop nav --}}
    <ul class="hidden lg:flex lg:items-center lg:gap-6">
        <li><a href="/" class="text-gray-700 hover:text-primary-600">Home</a></li>
        <li><a href="/about" class="text-gray-700 hover:text-primary-600">About</a></li>
    </ul>

    {{-- Mobile nav overlay --}}
    <div x-show="open" x-transition @click.away="open = false"
         class="absolute left-0 right-0 top-full z-50 bg-white shadow-lg lg:hidden">
        <ul class="space-y-2 p-4">
            <li><a href="/" class="block rounded-lg px-4 py-3 hover:bg-gray-100">Home</a></li>
            <li><a href="/about" class="block rounded-lg px-4 py-3 hover:bg-gray-100">About</a></li>
        </ul>
    </div>
</nav>
```

### Sidebar Layout
```blade
{{-- Fluid sidebar layout --}}
<div class="flex flex-col lg:flex-row lg:gap-8">
    <main class="flex-1">
        {{ $content }}
    </main>
    <aside class="order-2 mt-8 w-full lg:order-none lg:mt-0 lg:w-80">
        {{ $sidebar }}
    </aside>
</div>
```

## Image Optimization

Serve appropriate images for device size:

```blade
<picture>
    <source srcset="{{ asset('images/hero.avif') }}" type="image/avif">
    <source srcset="{{ asset('images/hero.webp') }}" type="image/webp">
    <img 
        src="{{ asset('images/hero-800x600.jpg') }}" 
        srcset="{{ asset('images/hero-400x300.jpg') }} 400w, {{ asset('images/hero-800x600.jpg') }} 800w, {{ asset('images/hero-1600x1200.jpg') }} 1600w"
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 800px"
        alt="Hero" 
        loading="lazy"
        class="h-auto w-full object-cover"
    >
</picture>
```

## Touch Targets

Ensure buttons meet minimum size (44x44px):

```blade
<button class="min-h-[44px] min-w-[44px] px-4 py-2 inline-flex items-center">
    {{ $label }}
</button>
```
