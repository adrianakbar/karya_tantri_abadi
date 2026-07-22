<?php

namespace Database\Seeders;

use App\Models\Permissions;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class PermissionAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default modules and their permissions
        $permissions = [
            'dashboard' => [
                'view_dashboard' => 'Melihat dashboard',
            ],
            'users' => [
                'view_users' => 'Melihat daftar pengguna',
                'create_users' => 'Membuat pengguna baru',
                'edit_users' => 'Mengubah data pengguna',
                'delete_users' => 'Menghapus pengguna',
            ],
            'roles' => [
                'view_roles' => 'Melihat daftar peran',
                'create_roles' => 'Membuat peran baru',
                'edit_roles' => 'Mengubah peran',
                'delete_roles' => 'Menghapus peran',
            ],
            'permissions' => [
                'view_permissions' => 'Melihat daftar hak akses',
                'manage_permissions' => 'Mengelola hak akses',
            ],
            'products' => [
                'view_products' => 'Melihat daftar produk',
                'create_products' => 'Membuat produk baru',
                'edit_products' => 'Mengubah produk',
                'delete_products' => 'Menghapus produk',
            ],
            'transactions' => [
                'view_transactions' => 'Melihat transaksi',
                'create_transactions' => 'Membuat transaksi baru',
                'edit_transactions' => 'Mengubah transaksi',
                'delete_transactions' => 'Menghapus transaksi',
            ],
            'reports' => [
                'view_reports' => 'Melihat laporan',
                'generate_reports' => 'Membuat laporan',
                'export_reports' => 'Mengekspor laporan',
            ],
            'settings' => [
                'view_settings' => 'Melihat pengaturan',
                'manage_settings' => 'Mengelola pengaturan sistem',
            ],
        ];

        // Create permissions
        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $name => $description) {
                Permissions::firstOrCreate(
                    ['name' => $name],
                    [
                        'module' => $module,
                        'description' => $description,
                    ]
                );
            }
        }

        // Create default roles (Karya Tantri Abadi)
                $roles = [
                    'admin' => [
                        'name' => 'admin',
                        'description' => 'Administrator — input pinjaman & kelola sistem',
                        'permissions' => '*', // All permissions
                    ],
                    'spv' => [
                        'name' => 'spv',
                        'description' => 'Supervisor — menyetujui/menolak pengajuan pinjaman',
                        'permissions' => [
                            'view_dashboard',
                            'view_users',
                            'view_transactions',
                            'edit_transactions',
                            'view_reports',
                            'generate_reports',
                            'export_reports',
                        ],
                    ],
                    'kasir' => [
                        'name' => 'kasir',
                        'description' => 'Kasir — mencairkan pinjaman & mencatat transaksi keuangan',
                        'permissions' => [
                            'view_dashboard',
                            'view_users',
                            'view_transactions',
                            'create_transactions',
                            'edit_transactions',
                            'view_reports',
                            'generate_reports',
                            'export_reports',
                        ],
                    ],
                    'anggota' => [
                        'name' => 'anggota',
                        'description' => 'Anggota — hanya melihat data pinjaman',
                        'permissions' => [
                            'view_dashboard',
                            'view_transactions',
                        ],
                    ],
                ];

        foreach ($roles as $roleKey => $roleData) {
            $cooperation = \App\Models\Cooperation::first();
            
            if (!$cooperation) {
                throw new \Exception('No cooperation found. Please run CooperationSeeder first.');
            }

            $role = Roles::firstOrCreate(
                ['name' => $roleData['name'], 'cooperation_id' => $cooperation->id],
                [
                    'description' => $roleData['description'],
                    'is_active' => true,
                ]
            );

            // Assign permissions to role
            if ($roleData['permissions'] === '*') {
                // Assign all permissions to admin
                $permissionIds = Permissions::pluck('id')->toArray();
            } else {
                // Assign specific permissions
                $permissionIds = Permissions::whereIn('name', $roleData['permissions'])->pluck('id')->toArray();
            }

            $role->permissions()->sync($permissionIds);
        }
    }
}
