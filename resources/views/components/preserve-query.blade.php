@php
    $exclude = $exclude ?? [];
    $query = request()->except($exclude);
@endphp

@foreach($query as $key => $value)
    @if(is_array($value))
        @foreach($value as $subKey => $subValue)
            <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
        @endforeach
    @else
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endif
@endforeach
