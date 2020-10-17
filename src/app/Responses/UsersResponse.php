<?php

namespace App\Responses;

use App\Models\User;

class UsersResponse extends JsonResponse
{
    protected array $users = [];

    public function __construct(array $collection)
    {
        foreach ($collection as $user) {
            if ($user instanceof User) {
                $this->$user[] = $user->toArray();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getBody()
    {
        return $this->users ?? [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(): string
    {
        return "users";
    }
}
