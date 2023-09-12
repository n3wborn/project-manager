<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class Archiver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Validator $validator,
        private Mapper $mapper,
        private ExceptionLogger $logger,
    ) {
    }

    public function softDelete(Project $project): void
    {
        $this->entityManager->persist($project->setArchivedAt(new \DateTimeImmutable()));
        $this->entityManager->flush();
    }

    public function process(?Project $project): JsonResponse
    {
        try {
            $this->validator->validateProjectIsArchivable($project);
            $this->softDelete($project);

            $response = ApiResponse::createAndFormat(
                Mapper::fromEntityToJson($project),
                ApiMessages::PROJECT_DELETE_SUCCESS_MESSAGE);
        } catch (NotFoundException|BadDataException $exception) {
            $this->logger->logNotice($exception);
            $response = ApiResponse::createWarningMessage($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->logCriticalAndTrace($exception);
            $response = ApiResponse::createErrorMessage(ApiMessages::DEFAULT_ERROR_MESSAGE, exception: $exception);
        }

        return $response;
    }
}
