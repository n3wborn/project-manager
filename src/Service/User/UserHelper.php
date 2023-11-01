<?php

namespace App\Service\User;

use App\Controller\UserController;
use App\Entity\Project;
use App\Entity\User;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class UserHelper
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public static function isEditRoute(Request $request): bool
    {
        return UserController::ROUTE_EDIT === $request->get('_route');
    }

    /** @throws NotFoundException */
    public function editSlugParamExists(Request $request): ?Project
    {
        return isset($request->get('_route_params')['slug'])
            ? $this->em->getRepository(User::class)->findOneBy(['slug' => $request->get('_route_params')['slug']])
            : null;
    }

    public static function generateEditSuccessMessage(Request $request): string
    {
        return self::isEditRoute($request)
            ? ApiMessages::USER_UPDATE_SUCCESS_MESSAGE
            : ApiMessages::USER_CREATE_SUCCESS_MESSAGE;
    }

    /** @throws NotFoundException|BadDataException */
    public function validateRequestResource(Request $request, User $user): void
    {
        (self::isEditRoute($request) && (null === $this->editSlugParamExists($request)))
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::USER_UNKNOWN));

        $user->isArchived()
            && throw new BadDataException(UserValidator::USER_ALREADY_ARCHIVED);
    }

    public static function userExists(?User $user): bool
    {
        return (!$user?->isArchived()) && (null !== $user);
    }

    public static function getProjectsArrayFromUser(User $user): array
    {
        return array_map(
            static fn (Project $project) => [
                'name' => $project->getName(),
                'slug' => $project->getSlug(),
            ],
            $user->getProjects()->toArray()
        );
    }
}
