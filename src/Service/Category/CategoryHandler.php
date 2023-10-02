<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Entity\Project;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use App\Service\Project\ProjectMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CategoryHandler
{
    public function __construct(
        private CategoryFinder $finder,
        private CategoryArchiver $archiver,
        private CategoryPersister $persister,
        private ExceptionLogger $logger,
    ) {
    }

    public function handleGetCategory(?Category $category): JsonResponse
    {
        try {
            null === $category
                && throw new NotFoundException(ApiMessages::translate(ApiMessages::CATEGORY_NOT_FOUND));

            $categoryInfos = $this->finder->get($category);
            $result = CategoryHelper::getCategoryAndProjectsInfos($categoryInfos);
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

    public function handlePersistCategory(?Category $category, Request $request, CategoryDTO $dto)
    {
        return $this->persister->processRequest($category ?? new Category(), $dto, $request);
    }

    public function handleGetAllCategories(): JsonResponse
    {
        $categories = $this->finder->getAllNotArchived();
        $result = array_map(static fn (Category $category) => CategoryMapper::fromEntityToJson($category), $categories);

        return new ApiResponse($result);
    }

    public function handleGetCategoryProjects(?Category $category)
    {
        try {
            $projects = $this->finder->getCategoryProjects($category);
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

    public function handleArchiveCategory(?Category $category)
    {
        return $this->archiver->process($category);
    }
}
