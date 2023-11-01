<?php

namespace App\Service\User;

use App\Entity\Project;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\UserRepository;

final class UserFinder
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function get(User $user): ?User
    {
        $user->isArchived()
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::USER_UNKNOWN));

        return
            (
                null !== ($result = $this->userRepository->find($user))
            )
            ? $result
            : null;
    }

    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function getAllNotArchived(): array
    {
        return $this->userRepository->findAllNotArchived();
    }

    /** @throws NotFoundException */
    public function getUserProjects(?User $user): array
    {
        (!UserHelper::userExists($user))
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::USER_UNKNOWN));

        return array_filter(
            $user->getProjects()->toArray(),
            fn (Project $project) => !$project->isArchived()
        );
    }
}
