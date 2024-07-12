<?php

namespace App\Service;

use App\Repository\TeamRepository;
use App\Repository\UserRepository;

class AppService
{
    public function __construct(private UserRepository $userRepository, private TeamRepository $teamRepository) {

    }
    public function getMenu()
    {
        return [
            ['route' => 'app_dashboard', 'label' => 'Dashboard'],
            ['route' => 'app_register', 'label' => 'Register User'],
            ['route' => 'app_user_index', 'label' => 'Users'],
            ['route' => 'app_team_index', 'label' => 'Teams'],
        ];
    }

    public function getAvailableUsers()
    {
        return array_map(function($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ];
        }, $this->userRepository->findAll());
    }
}