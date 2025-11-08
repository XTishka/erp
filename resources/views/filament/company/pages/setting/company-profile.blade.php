<x-filament-panels::page style="margin-bottom: 500px">
    <x-filament::section class="mb-6">
        <x-filament::section.heading>
            Profile
        </x-filament::section.heading>
        <x-filament::section.description>
            Select which company profile you want to edit.
        </x-filament::section.description>

        <div class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-auto">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="profileId">
                        @foreach ($this->profileOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            @if ($this->record?->is_default)
                <x-filament::badge color="success">
                    Default
                </x-filament::badge>
            @endif
        </div>
    </x-filament::section>

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('companyProfileUpdated', function () {
            window.location.reload();
        });
    });
</script>
