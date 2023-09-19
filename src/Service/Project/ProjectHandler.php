<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ProjectHandler
{
    public function __construct(
        private ProjectFinder $finder,
        private ExceptionLogger $logger,
        private ProjectArchiver $archiver,
        private ProjectPersister $persister,
    ) {
    }

    public function handleGetProject(?Project $project): JsonResponse
    {
        try {
            null === $project
                && throw new NotFoundException(ApiMessages::translate(ApiMessages::PROJECT_NOT_FOUND));

            $result = $this->finder->get($project);
            $response = new ApiResponse(ProjectMapper::fromEntityToJson($result));
        } catch (NotFoundException $exception) {
            $this->logger->logNotice($exception);
            $response = ApiResponse::createWarningMessage($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->logCriticalAndTrace($exception);
            $response = ApiResponse::createErrorMessage(ApiMessages::DEFAULT_ERROR_MESSAGE, exception: $exception);
        }

        return $response;
    }

    public function handleGetAllProjects(): JsonResponse
    {
        $projects = $this->finder->getAllNotArchived();
        $result = array_map(static fn (Project $project) => ProjectMapper::fromEntityToJson($project), $projects);

        return new ApiResponse($result);
    }

    public function handlePersistProject(?Project $project, Request $request, ProjectDTO $dto): JsonResponse
    {
        return $this->persister->processRequest($project ?? new Project(), $dto, $request);
    }

    public function handleArchiveProject(?Project $project): JsonResponse
    {
        return $this->archiver->process($project);
    }
}
