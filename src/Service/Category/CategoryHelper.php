<?php

namespace App\Service\Category;

use App\Controller\CategoryController;
use App\Entity\Category;
use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class CategoryHelper
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public static function isEditRoute(Request $request): bool
    {
        return CategoryController::ROUTE_EDIT === $request->get('_route');
    }

    /** @throws NotFoundException  */
    public function editSlugParamExists(Request $request): ?Category
    {
        return isset($request->get('_route_params')['slug'])
            ? $this->em->getRepository(Category::class)->findOneBy(['slug' => $request->get('_route_params')['slug']])
            : null;
    }

    public static function generateEditSuccessMessage(Request $request): string
    {
        return self::isEditRoute($request)
            ? ApiMessages::CATEGORY_UPDATE_SUCCESS_MESSAGE
            : ApiMessages::CATEGORY_CREATE_SUCCESS_MESSAGE;
    }

    /** @throws NotFoundException|BadDataException  */
    public function validateRequestResource(Request $request, category $category): void
    {
        (self::isEditRoute($request) && (null === $this->editSlugParamExists($request)))
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::CATEGORY_UNKNOWN));

        $category->isArchived()
            && throw new BadDataException(CategoryValidator::CATEGORY_ALREADY_ARCHIVED);
    }

    public static function findNotArchivedFromProject(Project $project): array
    {
        return array_map(
            static fn (Category $category) => !$category->isArchived() ? $category : null,
            $project->getCategories()->toArray()
        );
    }

    public static function categoryExists(?Category $category): bool
    {
        return (!$category->isArchived()) && (null !== $category);
    }
}
