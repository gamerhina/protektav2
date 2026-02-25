@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $brandingSettings = \App\Models\LandingPageSetting::first();
    $logoUrl = optional($brandingSettings)->logo_url;
    $appName = optional($brandingSettings)->app_name ?? config('app.name');
@endphp
@if($logoUrl)
    <img src="{{ $logoUrl }}" class="logo" alt="{{ $appName }} Logo" style="max-height: 50px; width: auto; max-width: 100%;">
@else
    {{ $appName }}
@endif
</a>
</td>
</tr>
