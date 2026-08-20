<x-filament-panels::page>
    <div x-data="{ tab: @js($activeTab) }">
        <x-filament::tabs>
            <x-filament::tabs.item alpine-active="tab === 'role'" x-on:click="tab = 'role'">Peran</x-filament::tabs.item>
            <x-filament::tabs.item alpine-active="tab === 'permission'" x-on:click="tab = 'permission'">Hak Akses</x-filament::tabs.item>
            <x-filament::tabs.item alpine-active="tab === 'userrole'" x-on:click="tab = 'userrole'">Peran Pengguna</x-filament::tabs.item>
            <x-filament::tabs.item alpine-active="tab === 'setting'" x-on:click="tab = 'setting'">Pengaturan Sistem</x-filament::tabs.item>
            <x-filament::tabs.item alpine-active="tab === 'backup'" x-on:click="tab = 'backup'">Backup Data</x-filament::tabs.item>
        </x-filament::tabs>

        <div class="mt-6">
            <div x-show="tab === 'role'">@livewire(\App\Filament\Resources\RoleResource\Pages\ListRoles::class)</div>
            <div x-show="tab === 'permission'">@livewire(\App\Filament\Resources\PermissionResource\Pages\ListPermissions::class)</div>
            <div x-show="tab === 'userrole'">@livewire(\App\Filament\Resources\UserRoleResource\Pages\ListUserRoles::class)</div>
            <div x-show="tab === 'setting'">@livewire(\App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings::class)</div>
            <div x-show="tab === 'backup'">@livewire(\App\Filament\Pages\BackupManagement::class)</div>
        </div>
    </div>
</x-filament-panels::page>
