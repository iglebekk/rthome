@props(['club', 'memberOptions', 'position' => null])

<x-app.card>
    <x-form :action="$position ? route('clubs.positions.update', [$club, $position]) : route('clubs.positions.store', $club)" :method="$position ? 'PUT' : 'POST'">
        <x-form.input name="name" :label="__('positions.fields.name')" :value="old('name', $position?->name)" required autofocus />
        <x-form.select name="member_id" :label="__('positions.fields.member')" :options="$memberOptions" :value="old('member_id', $position?->member_id)" :placeholder="__('positions.select_member')" />
        <x-form.input name="sort_order" type="number" min="0" :label="__('positions.fields.sort_order')" :value="old('sort_order', $position?->sort_order)" />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="start_date" type="date" :label="__('positions.fields.start_date')" :value="old('start_date', $position?->start_date?->toDateString())" />
            <x-form.input name="end_date" type="date" :label="__('positions.fields.end_date')" :value="old('end_date', $position?->end_date?->toDateString())" />
        </div>
        <x-form.actions>
            <x-app.link-button :href="route('clubs.positions.index', $club)" variant="ghost">{{ __('app.actions.cancel') }}</x-app.link-button>
            <x-app.button type="submit">{{ __('app.actions.save') }}</x-app.button>
        </x-form.actions>
    </x-form>
</x-app.card>
