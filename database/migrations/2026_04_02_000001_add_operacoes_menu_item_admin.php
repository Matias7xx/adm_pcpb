<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Evita duplicata
        if (DB::table('menu_items')->where('uri', '/<admin>/operacoes-admin')->exists()) {
            return;
        }

        $menuId = DB::table('menus')->where('machine_name', 'admin')->value('id');

        if (!$menuId) {
            return;
        }

        DB::table('menu_items')->insert([
            'menu_id'    => $menuId,
            'name'       => 'Operações',
            'uri'        => '/<admin>/operacoes-admin',
            'enabled'    => true,
            'weight'     => 7,
            'icon'       => 'M19,3H14.82C14.4,1.84 13.3,1 12,1C10.7,1 9.6,1.84 9.18,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M12,3A1,1 0 0,1 13,4A1,1 0 0,1 12,5A1,1 0 0,1 11,4A1,1 0 0,1 12,3M7,7H17V9H7V7M7,11H17V13H7V11M7,15H14V17H7V15Z',
            'roles'      => json_encode([['name' => 'super-admin'], ['name' => 'diop']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $menu = Menu::where('machine_name', 'admin')->first();
        DB::table('menu_items')->where('uri', '/<admin>/operacoes-admin')->delete();
    }
};
