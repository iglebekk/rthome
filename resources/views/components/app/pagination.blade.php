@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes }}>{{ $paginator->links() }}</div>
@endif
