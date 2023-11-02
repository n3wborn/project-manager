<?php

namespace App\Service\User;

use App\Entity\Project;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use App\Service\Project\ProjectMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserHandler
{
    public function __construct(
        private UserFinder $finder,
        private ExceptionLogger $logger,
        private UserArchiver $archiver,
        private UserPersister $persister,
    ) {
    }

    public function handleGetUserInfos(?User $user): JsonResponse
    {
        try {
            null === $user
                && throw new NotFoundException(ApiMessages::translate(ApiMessages::USER_NOT_FOUND));

            $userInfos = $this->finder->get($user);
            $result = UserFetchMapper::fromEntityToJson($userInfos);
            $response = new ApiResponse($result);
        } catch (NotFoundException $exception) {
            $this->logger->logNotice($exception);
            $response = ApiResponse::createWarningMessage($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->logCriticalAndTrace($exception);
            $response = ApiResponse::createErrorMessage(ApiMessages::DEFAULT_ERROR_MESSAGE, exception: $exception);
        }

        return $response;
    }

    public function handleGetAllUsers(): JsonResponse
    {
        $users = $this->finder->getAllNotArchived();
        $result = array_map(static fn (User $user) => UserMapper::fromEntityToJson($user), $users);

        return new ApiResponse($result);
    }

    public function handlePersistUser(?User $user, Request $request, UserDTO $dto): JsonResponse
    {
        return $this->persister->processRequest($user ?? new User(), $dto, $request);
    }

    public function handleArchiveUser(?User $user): JsonResponse
    {
        return $this->archiver->process($user);
    }

    public function handleGetUserProjects(?User $user): JsonResponse
    {
        try {
            $projects = $this->finder->getUserProjects($user);
            $result = array_map(static fn (Project $project) => ProjectMapper::fromEntityToJson($project), $projects);
            $response = new ApiResponse($result);
        } catch (NotFoundException $exception) {
            $this->logger->logNotice($exception);
            $response = ApiResponse::createWarningMessage($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->logCriticalAndTrace($exception);
            $response = ApiResponse::createErrorMessage(ApiMessages::DEFAULT_ERROR_MESSAGE, exception: $exception);
        }

        return $response;
    }
}
