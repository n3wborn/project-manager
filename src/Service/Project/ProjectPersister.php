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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

final class ProjectPersister
{
    public function __construct(
        private ProjectValidator $validator,
        private EntityManagerInterface $em,
        private ProjectHelper $helper,
        private ExceptionLogger $logger,
        private SerializerInterface $serializer,
    ) {
    }

    public function processRequest(Project $project, ProjectDTO $dto, Request $request): JsonResponse
    {
        try {
            $this->helper->validateRequestResource($request, $project);
            $this->validator->validate($dto, $this->helper->isEditRoute($request));
            $this->persist($project, $dto);

            $response = ApiResponse::createAndFormat(
                ProjectMapper::fromEntityToJson($project),
                ProjectHelper::generateEditSuccessMessage($request)
            );
        } catch (NotFoundException|BadDataException $exception) {
            $this->logger->logNotice($exception);
            $response = ApiResponse::createWarningMessage($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->logCriticalAndTrace($exception);
            $response = ApiResponse::createErrorMessage(ApiMessages::DEFAULT_ERROR_MESSAGE, exception: $exception);
        }

        return $response;
    }

    /** @throws NotFoundException  */
    public function persist(?Project $project, ProjectDTO $dto): void
    {
        $project
            ->setName($dto->getName())
            ->setDescription($dto->getDescription());

        $this->em->persist($project);
        $this->em->flush();
    }
}
