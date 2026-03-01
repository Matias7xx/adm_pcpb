<?php

namespace Database\Seeders;

use BalajiDharma\LaravelCategory\Models\CategoryType;
use BalajiDharma\LaravelMenu\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminCoreSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'admin user',
            'permission list',
            'permission create',
            'permission edit',
            'permission delete',
            'role list',
            'role create',
            'role edit',
            'role delete',
            'user list',
            'user create',
            'user edit',
            'user delete',
            'menu list',
            'menu create',
            'menu edit',
            'menu delete',
            'menu.item list',
            'menu.item create',
            'menu.item edit',
            'menu.item delete',
            'category list',
            'category create',
            'category edit',
            'category delete',
            'category.type list',
            'category.type create',
            'category.type edit',
            'category.type delete',
            'media list',
            'media create',
            'media edit',
            'media delete',
            'comment list',
            'comment create',
            'comment edit',
            'comment delete',
            'thread list',
            'thread create',
            'thread edit',
            'thread delete',
            'curso list',
            'curso create',
            'curso edit',
            'curso delete',
            'director list',
            'director create',
            'director edit',
            'director delete',
            'noticia list',
            'noticia create',
            'noticia edit',
            'noticia delete',
            'video list',
            'video create',
            'video edit',
            'video delete',
            'banner list',
            'banner create',
            'banner edit',
            'banner delete',
            'veiculo list',
            'veiculo create',
            'veiculo edit',
            'veiculo delete',
            'audit_log list',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // super-admin: acesso total via Gate::before (AuthServiceProvider)
        $roleSuperAdmin = Role::create(['name' => 'super-admin']);

        // admin: todas as permissões
        $roleAdmin = Role::create(['name' => 'admin']);
        foreach ($permissions as $permission) {
            $roleAdmin->givePermissionTo($permission);
        }

        // servidor: apenas permissões de listagem
        $roleServidor = Role::create(['name' => 'servidor']);
        foreach ($permissions as $permission) {
            if (Str::contains($permission, 'list')) {
                $roleServidor->givePermissionTo($permission);
            }
        }

        // diop: acesso admin + somente veículos
        $roleDiop = Role::create(['name' => 'diop']);
        $roleDiop->givePermissionTo([
            'admin user',
            'veiculo list',
            'veiculo create',
            'veiculo edit',
            'veiculo delete',
        ]);

        // create demo users
        $user = \App\Models\User::factory()->create([
            'name'      => 'Super Admin',
            'email'     => 'superadmin@example.com',
            'matricula' => '0000000',
        ]);
        $user->assignRole($roleSuperAdmin);

        $user = \App\Models\User::factory()->create([
            'name'      => 'Admin User',
            'email'     => 'admin@example.com',
            'matricula' => '0000001',
        ]);
        $user->assignRole($roleAdmin);

        $user = \App\Models\User::factory()->create([
            'name'      => 'Example User',
            'email'     => 'test@example.com',
            'matricula' => '0000002',
        ]);
        $user->assignRole($roleServidor);

        // ─── Menu Admin ───────────────────────────────────────────────────────
        // Regra: roles = null → visível para qualquer admin autenticado (incluindo diop)
        //        roles = JSON → visível apenas para as roles listadas
        //
        // Diop vê: Dashboard e Veículos (ambos com roles = null)
        // Admin vê: tudo exceto Logs de Auditoria
        // Super-admin vê: tudo

        $apenasAdmins    = json_encode([['name' => 'super-admin'], ['name' => 'admin']]);
        $apenasSuperAdmin = json_encode([['name' => 'super-admin']]);

        $menu = Menu::create([
            'name'         => 'Admin',
            'machine_name' => 'admin',
            'description'  => 'Admin Menu',
        ]);

        $menu->menuItems()->createMany([
            [
                // null = sem restrição de role → diop também vê
                'name'    => 'Dashboard',
                'uri'     => '/<admin>',
                'enabled' => 1,
                'weight'  => 0,
                'icon'    => 'M13 9V3H21V9H13M13 21H21V11H13M3 21H11V15H3M3 13H11V3H3V13Z',
                'roles'   => null,
            ],
            [
                'name'    => 'Funções',
                'uri'     => '/<admin>/role',
                'enabled' => 1,
                'weight'  => 1,
                'icon'    => 'M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7A2,2 0 0,1 14,9A2,2 0 0,1 12,11A2,2 0 0,1 10,9A2,2 0 0,1 12,7M12,14C13.25,14 15.42,14.57 16,15.56V18.18L12,21L8,18.18V15.56C8.58,14.57 10.75,14 12,14Z',
                'roles'   => $apenasSuperAdmin,
            ],
            [
                'name'    => 'Usuários',
                'uri'     => '/<admin>/user',
                'enabled' => 1,
                'weight'  => 2,
                'icon'    => 'M16 17V19H2V17S2 13 9 13 16 17 16 17M12.5 7.5A3.5 3.5 0 1 0 9 11A3.5 3.5 0 0 0 12.5 7.5M15.94 13A5.32 5.32 0 0 1 18 17V19H22V17S22 13.37 15.94 13M15 4A3.39 3.39 0 0 0 13.07 4.59A5 5 0 0 1 13.07 10.41A3.39 3.39 0 0 0 15 11A3.5 3.5 0 0 0 15 4Z',
                'roles'   => $apenasSuperAdmin,
            ],
            [
                'name'    => 'Notícias',
                'uri'     => '/<admin>/noticias',
                'enabled' => 1,
                'weight'  => 3,
                'icon'    => 'M20,3H4A2,2 0 0,0 2,5V19A2,2 0 0,0 4,21H20A2,2 0 0,0 22,19V5A2,2 0 0,0 20,3M20,19H4V5H20V19M6,7H18V9H6V7M6,11H18V13H6V11M6,15H13V17H6V15Z',
                'roles'   => $apenasAdmins,
            ],
            [
                'name'    => 'Vídeos',
                'uri'     => '/<admin>/videos',
                'enabled' => 1,
                'weight'  => 4,
                'icon'    => 'M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z',
                'roles'   => $apenasAdmins,
            ],
            [
                'name'    => 'Banners',
                'uri'     => '/<admin>/banners',
                'enabled' => 1,
                'weight'  => 5,
                'icon'    => 'M21,3H3A2,2 0 0,0 1,5V19A2,2 0 0,0 3,21H21A2,2 0 0,0 23,19V5A2,2 0 0,0 21,3M21,19H3V5H21V19M5,7H11V13H5V7M13,7H19V9H13V7M13,11H19V13H13V11M5,15H19V17H5V15Z',
                'roles'   => $apenasAdmins,
            ],
            [
                // null = sem restrição de role → diop também vê
                'name'    => 'Veículos',
                'uri'     => '/<admin>/veiculo',
                'enabled' => 1,
                'weight'  => 6,
                'icon'    => 'M18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5M19.5,9.5L21.46,12H17V9.5M6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5M20,8H17V4H3C1.89,4 1,4.89 1,6V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V12L20,8Z',
                'roles'   => null,
            ],
            [
                'name'    => 'Logs',
                'uri'     => '/<admin>/audit-logs',
                'enabled' => 1,
                'weight'  => 99,
                'icon'    => 'M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1Z',
                'roles'   => $apenasSuperAdmin,
            ],
        ]);

        // ─── Menu Servidor ────────────────────────────────────────────────────
        $servidorMenu = Menu::create([
            'name'         => 'Servidor',
            'machine_name' => 'servidor',
            'description'  => 'Servidor Menu',
        ]);

        $servidorMenu->menuItems()->createMany([
            [
                'name'    => 'Meus Cursos',
                'uri'     => '/<servidor>',
                'enabled' => 1,
                'weight'  => 0,
                'icon'    => 'M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z',
            ],
        ]);

        // ─── Category Types ───────────────────────────────────────────────────
        CategoryType::create([
            'name'         => 'Categoria',
            'machine_name' => 'category',
            'description'  => 'Main Category',
        ]);

        CategoryType::create([
            'name'         => 'Tag',
            'machine_name' => 'tag',
            'description'  => 'Site Tags',
            'is_flat'      => true,
        ]);

        CategoryType::create([
            'name'         => 'Admin Tag',
            'machine_name' => 'admin_tag',
            'description'  => 'Admin Tags',
            'is_flat'      => true,
        ]);

        $forumCategoryType = CategoryType::create([
            'name'         => 'Fórum Categoria',
            'machine_name' => 'forum_category',
            'description'  => 'Forum Category',
        ]);

        $forumCategoryType->categories()->create([
            'name'        => 'Geral',
            'description' => 'Fórum Geral',
        ]);

        CategoryType::create([
            'name'         => 'Forum Tag',
            'machine_name' => 'forum_tag',
            'description'  => 'Forum Tags',
            'is_flat'      => true,
        ]);
    }
}