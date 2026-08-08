@props(['action', 'method' => 'POST', 'enctype' => null])

<form action="{{ $action }}" method="{{ in_array(strtoupper($method), ['GET', 'POST']) ? strtoupper($method) : 'POST' }}" @if($enctype) enctype="{{ $enctype }}" @endif {{ $attributes->class('grid gap-6') }}>
    @unless (strtoupper($method) === 'GET')
        @csrf
    @endunless
    @unless (in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endunless
    {{ $slot }}
</form>
