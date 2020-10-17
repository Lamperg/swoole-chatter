<?php

namespace App\Utilities;

use App\Models\User;
use App\Repositories\UserRepository;

class Authenticator
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Logs in provided user.
     *
     * @param User $user
     */
    public function login(User $user): void
    {
        if (!$this->isLoggedIn($user)) {
            $this->userRepository->add($user);
        }
    }

    /**
     * Logs out provided user.
     *
     * @param User $user
     */
    public function logout(User $user): void
    {
        $this->userRepository->delete($user->getConnectionId());
    }

    /**
     * Checks if provided user is already logged in.
     *
     * @param User $user
     * @return bool
     */
    public function isLoggedIn(User $user): bool
    {
        $loggedUser = $this->userRepository->getById($user->getConnectionId());

        if ($loggedUser) {
            return $user->getUsername() === $loggedUser->getUsername();
        }

        return false;
    }

    /**
     * Checks if provided username has already been taken by another user.
     *
     * @param string $username
     * @return bool
     */
    public function isUsernameUsed(string $username): bool
    {
        foreach ($this->userRepository->all() as $onlineUser) {
            if ($username === $onlineUser->getUsername()) {
                return true;
            }
        }

        return false;
    }
}
