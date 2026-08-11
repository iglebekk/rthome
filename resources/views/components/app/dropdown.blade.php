@props(['position' => 'bottom', 'align' => 'end'])

<flux:dropdown :$position :$align {{ $attributes }}>
    {{ $trigger }}
    {{ $menu }}
</flux:dropdown>
