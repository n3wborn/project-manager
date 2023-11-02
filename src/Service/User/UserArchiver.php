<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UserArchiver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserValidator $validator,
        private UserMapper $mapper,
        private ExceptionLogger $logger,
        private UserPersister $persister,
    ) {
    }

    public function softDelete(User $user): void
    {
        $this->entityManager->persist($user->setArchivedAt(new \DateTimeImmutable()));
        $this->entityManager->flush();
    }

    public function process(?User $user): JsonResponse
    {
        try {
            $this->validator->validateUserIsArchivable($user);
            $this->softDelete($user);

            $response = ApiResponse::createAndFormat(
                UserMapper::fromEntityToJson($user),
                ApiMessages::USER_ARCHIVE_SUCCESS_MESSAGE);
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
