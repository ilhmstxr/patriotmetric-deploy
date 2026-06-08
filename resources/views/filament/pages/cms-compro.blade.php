<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6 flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button type="submit" wire:loading.attr="disabled">
                        Simpan Perubahan
                    </x-filament::button>

                    <span wire:loading wire:target="save" class="text-sm text-gray-500">
                        Menyimpan...
                    </span>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
