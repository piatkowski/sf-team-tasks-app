<?php

namespace App\Service;

class AppService
{
    public function getMenu()
    {
        return [
            ['route' => 'app_dashboard', 'label' => 'Dashboard'],
            ['route' => 'app_register', 'label' => 'Register User'],
            ['route' => 'app_user_index', 'label' => 'Users'],
            ['route' => 'app_team_index', 'label' => 'Teams'],
        ];
    }
}