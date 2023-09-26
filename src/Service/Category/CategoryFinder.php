<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Entity\Project;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class CategoryFinder
{
    public function __construct(
        private ExceptionLogger $logger,
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function get(Category $category): ?Category
    {
        $category->isArchived()
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::CATEGORY_NOT_FOUND));

        return
            (
                null !== ($result = $this->categoryRepository->find($category))
            )
            ? $result
            : null;
    }

    public function getAllFromProject(?Project $project)
    {
        try {
            !$project && throw new NotFoundException(ApiMessages::PROJECT_UNKNOWN);
            $data = CategoryHelper::findNotArchivedFromProject($project);
            $result = array_map(fn (Category $category) => CategoryMapper::fromEntityToJson($category), $data);
            $response = ApiResponse::createAndFormat($result);
        } catch (ExceptionInterface|NotFoundException $exception) {
            $this->logger->logNotice($exception);
            $response = ApiResponse::createWarningMessage(ApiMessages::LISTING_ERROR);
        } catch (\Exception $exception) {
            $this->logger->logCriticalAndTrace($exception);
            $response = ApiResponse::createErrorMessage(ApiMessages::DEFAULT_ERROR_MESSAGE, exception: $exception);
        }

        return $response;
    }

    public function getAllNotArchived()
    {
        return $this->categoryRepository->findAllNotArchived();
    }

    public function getCategoryProjects(?Category $category): array
    {
        (!CategoryHelper::categoryExists($category))
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::CATEGORY_NOT_FOUND));

        return array_filter(
            $category->getProjects()->toArray(),
            fn (Project $project) => !$project->isArchived()
        );
    }
}
