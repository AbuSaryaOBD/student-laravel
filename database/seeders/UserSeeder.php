<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Role::create(['name' => 'Admin']);
        $student = Role::create(['name' => 'Student']);

        $obada = User::factory()->createOne([
            'name' => 'عبادة',
            'email' => 'obada@test.com'
        ]);

        $ahmad = User::factory()->createOne([
            'name' => 'أحمد',
            'email' => 'ahmad@test.com'
        ]);
        $obada->assignRole($admin);
        $ahmad->assignRole($admin);

        $studetns = User::factory(250)->create();
        $student->users()->attach($studetns);
    }
}
