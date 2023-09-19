<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Profile;
use App\Models\Settings;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        User::truncate();

        // $this->call(PermissionSeeder::class);
        $this->call([
            PermissionSeeder::class,
        ]);
        $superAdminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'sanctum', 'priority' => 1]);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'sanctum', 'priority' => 2]);
        $teacherRole = Role::create(['name' => 'teacher', 'guard_name' => 'sanctum', 'priority' => 3]);
        $studentRole = Role::create(['name' => 'student', 'guard_name' => 'sanctum', 'priority' => 4]);
        $marketingRole = Role::create(['name' => 'marketing', 'guard_name' => 'sanctum', 'priority' => 5]);
        // $patientRole = Role::create(['name' => 'patient', 'guard_name' => 'sanctum', 'priority' => 6]);

        //for super admin
        $superadmin = \App\Models\User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@mail.com',
            'password' => bcrypt('Asdf!234'),
            'status' => 1
        ]);
        $superadmin->assignRole('superadmin');

        // Give all permission to superadmin
        $permissions = Permission::all();
        $permissions->each(function ($permission, $key) use ($superAdminRole){
            // $superAdminRole->givePermissionTo($permission->name);
            $permission->assignRole($superAdminRole);
        });

        //for Admin
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            // 'phone' => '01681493936',
            'password' => bcrypt('Asdf!234'),
            'status' => 1
        ]);
        $admin->assignRole('admin');

        //Role
        // $superAdminRole->givePermissionTo('all-device');

        // $adminRole->givePermissionTo('publish-device');
        // $adminRole->givePermissionTo('add-device');
        // $adminRole->givePermissionTo('unpublish-device');

        // Member
        $users = User::factory(100)->create();

        $users->each(function ($user, $key){
            if ($key < 10) {
                $user->assignRole('teacher');
            } elseif ($key < 90) {
                $user->assignRole('student');
            } else {
                $user->assignRole('marketing');
            }
        });

        // Profile
        $users = User::all();

        foreach ($users as $user) {
            Profile::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        $settings = Settings::create(['settings_key' => 'site_name', 'settings_value' => 'Dace', 'settings_type' => 'string']);
        $settings = Settings::create(['settings_key' => 'logo', 'settings_value' => 'https://i0.wp.com/www.additudemag.com/wp-content/uploads/2006/12/GettyImages-1129223269.jpg?w=300&crop=0%2C0px%2C100%2C300px&ssl=1', 'settings_type' => 'string']);
    }
}
