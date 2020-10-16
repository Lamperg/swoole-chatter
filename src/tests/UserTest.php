<?php

namespace Tests;

use App\Models\User;
use PHPUnit\Framework\TestCase;
use App\Repositories\UserRepository;

class UserTest extends TestCase
{
    /**
     * @test
     */
    public function onlineUserCanBeAddedToMemoryTable()
    {
        $userRepository = new UserRepository();
        $user = new User('test', 1);

        $userRepository->add($user);
        $addedUser = $userRepository->getById(1);

        $this->assertInstanceOf(User::class, $addedUser);
        $this->assertEquals('test', $addedUser->getUsername());
    }

    /**
     * @test
     */
    public function onlineUserCanBeRemovedFromMemoryTable()
    {
        $userRepository = new UserRepository();
        $user = new User('test', 1);

        $userRepository->add($user);
        $userRepository->delete($user->getConnectionId());

        $this->assertArrayNotHasKey(1, $userRepository->getAll());
    }
}
