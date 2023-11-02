<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\User\UserDTO;
use App\Service\User\UserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private UserHandler $handler,
    ) {
    }

    public const ROUTE_ADD = 'user_add';
    public const ROUTE_ARCHIVE = 'user_archive';
    public const ROUTE_EDIT = 'user_edit';
    public const ROUTE_FETCH = 'user_fetch';
    public const ROUTE_FIND_ALL = 'user_find_all';
    public const ROUTE_GET_PROJECTS = 'user_get_projects';

    #[Route('/user/{slug}', name: self::ROUTE_FETCH, methods: Request::METHOD_GET)]
    public function getUserInfos(?User $user): JsonResponse
    {
        return $this->handler->handleGetUserInfos($user);
    }

    #[Route('/user', name: self::ROUTE_ADD, methods: Request::METHOD_POST)]
    #[Route('/user/{slug}', name: self::ROUTE_EDIT, methods: Request::METHOD_POST)]
    public function persistUser(
        ?User $user,
        #[MapRequestPayload()] ?UserDTO $dto,
        Request $request,
    ): JsonResponse {
        return $this->handler->handlePersistUser($user, $request, $dto);
    }

    #[Route('/users', name: self::ROUTE_FIND_ALL, methods: Request::METHOD_GET)]
    public function getAllusers(): JsonResponse
    {
        return $this->handler->handleGetAllUsers();
    }

    #[Route('/user/{slug}', name: self::ROUTE_ARCHIVE, methods: Request::METHOD_DELETE)]
    public function archiveUser(?User $user): JsonResponse
    {
        return $this->handler->handleArchiveUser($user);
    }

    #[Route('/user/{slug}/projects', name: self::ROUTE_GET_PROJECTS, methods: Request::METHOD_GET)]
    public function getProjects(
        ?User $user,
    ): JsonResponse {
        return $this->handler->handleGetUserProjects($user);
    }
}
