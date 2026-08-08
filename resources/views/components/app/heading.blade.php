@props(['size' => null, 'level' => null])

<flux:heading :$size :$level {{ $attributes }}>{{ $slot }}</flux:heading>
