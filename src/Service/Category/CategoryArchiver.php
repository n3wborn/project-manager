<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CategoryArchiver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryValidator $validator,
        private CategoryMapper $mapper,
        private ExceptionLogger $logger,
    ) {
    }

    public function softDelete(Category $category): void
    {
        $this->entityManager->persist($category->setArchivedAt(new \DateTimeImmutable()));
        $this->entityManager->flush();
    }

    public function process(?Category $category): JsonResponse
    {
        try {
            $this->validator->validateCategoryIsArchivable($category);
            $this->softDelete($category);

            $response = ApiResponse::createAndFormat(
                CategoryMapper::fromEntityToJson($category),
                ApiMessages::CATEGORY_DELETE_SUCCESS_MESSAGE);
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
