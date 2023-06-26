<?php

use Illuminate\Database\Seeder;
use App\Repositories\SettingsRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;

class ManualInstall extends Seeder
{
    private $settingsRepository;

    private $userRepository;

    public function run()
    {
        $first_name = 'admin';
        $last_name = 'admin';
        $email = 'admin@admin.com';
        $password = 'password';

        $this->settingsRepository = new SettingsRepositoryEloquent(app());
        $this->userRepository = new UserRepositoryEloquent(app());

        $this->settingsRepository->setKey('site_name', 'facturazzion_saas');

        $this->settingsRepository->setKey('site_email', 'saas@facturazzion.com');

        $this->settingsRepository->setKey('currency', 'MXN');

        $admin = Sentinel::register([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password,
            'user_id' => 1,
        ], true);

        $this->userRepository->assignRole($admin, 'admin');

        $this->command->info('New user with email :'.$admin->email.' created');

        $my_file = base_path().'/storage/installed';
        $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
        $data = 'facturazzion saas successfully installed';
        fwrite($handle, $data);

        \Artisan::call('config:cache');
        \Artisan::call('route:cache');

        $this->command->info('facturazzion saas Installed succesfully');
    }
}
