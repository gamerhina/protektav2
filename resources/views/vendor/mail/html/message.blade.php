<x-mail::layout>
{{-- Header --}}
<x-slot:header>
@php
    $brandingSettings = \App\Models\LandingPageSetting::first();
    $appName = optional($brandingSettings)->app_name ?? config('app.name');
@endphp
<x-mail::header :url="config('app.url')">
{{ $appName }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} Bihikmi. All rights reserved.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
