@props(['name'])
<svg {{ $attributes->merge(['width' => '20', 'height' => '20', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }}
    aria-hidden="true" focusable="false" style="flex-shrink:0">
    @switch($name)
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
        @break

        @case('close')
            <path d="m6 6 12 12M18 6 6 18" />
        @break

        @case('chevron-left')
            <path d="m14 6-6 6 6 6" />
        @break

        @case('chevron-right')
            <path d="m10 6 6 6-6 6" />
        @break

        @case('download')
            <path d="M12 3v12m-5-5 5 5 5-5M5 16v4h14v-4" />
        @break
    @endswitch
</svg>
