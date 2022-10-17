<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\hasPermission;
use App\Models\permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $admin = User::create([
            'name' => 'abdelhak ait elhad',
            'email' => 'admin@yobi.com',
            'password' => Hash::make('99par1999'),
        ]);
        DB::table('permissions')->insert([
            ["code" => 200, "description" => 'able to edit/delete/create new staff', 'created_at' => now(), 'updated_at' => now()],
            ["code" => 203, "description" => 'able to edit/delete/create new stors', 'created_at' => now(), 'updated_at' => now()],
            ["code" => 204, "description" => 'able to edit/delete/create new landing pages', 'created_at' => now(), 'updated_at' => now()],
            ["code" => 205, "description" => 'able to treat/edit/delete/create new orders', 'created_at' => now(), 'updated_at' => now()],
            ["code" => 206, "description" => 'able to push orders to delivry system', 'created_at' => now(), 'updated_at' => now()],
            ["code" => 207, "description" => 'able to edit/delete/create new pallets', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $permissions = permission::all();
        foreach ($permissions as $key => $permission) {
            hasPermission::create(
                ["id_user" => $admin->id, "id_permission" => $permission->id]
            );
        }

    }
}