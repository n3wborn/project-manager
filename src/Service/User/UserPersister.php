<?php

namespace App\Service\User;

use App\Entity\Project;
use App\Entity\User;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserPersister
{
    public function __construct(
        private UserValidator $validator,
        private EntityManagerInterface $em,
        private UserHelper $helper,
        private ExceptionLogger $logger,
    ) {
    }

    public function processRequest(User $project, UserDTO $dto, Request $request): JsonResponse
    {
        try {
            $this->helper->validateRequestResource($request, $project);
            $this->validator->validate($dto, $this->helper->isEditRoute($request));
            $this->persist($project, $dto);

            $response = ApiResponse::createAndFormat(
                UserMapper::fromEntityToJson($project),
                UserHelper::generateEditSuccessMessage($request)
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

    public function persist(User $user, UserDTO $dto): void
    {
        $user->setEmail($dto->getEmail());

        foreach ($user->getProjects() as $project) {
            $user->removeProject($project);
        }

        foreach ($dto->getProjects() as $dtoProject) {
            $project = $this->em->getRepository(Project::class)->findOneBySlug($dtoProject['slug']);
            $user->addProject($project);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}
