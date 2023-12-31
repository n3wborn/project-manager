<?php

namespace App\Service\Project;

use App\Controller\ProjectController;
use App\Entity\Category;
use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProjectHelper
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public static function isEditRoute(Request $request): bool
    {
        return ProjectController::ROUTE_EDIT === $request->get('_route');
    }

    /** @throws NotFoundException */
    public function editSlugParamExists(Request $request): ?Project
    {
        return isset($request->get('_route_params')['slug'])
            ? $this->em->getRepository(Project::class)->findOneBy(['slug' => $request->get('_route_params')['slug']])
            : null;
    }

    public static function generateEditSuccessMessage(Request $request): string
    {
        return self::isEditRoute($request)
            ? ApiMessages::PROJECT_UPDATE_SUCCESS_MESSAGE
            : ApiMessages::PROJECT_CREATE_SUCCESS_MESSAGE;
    }

    /** @throws NotFoundException|BadDataException */
    public function validateRequestResource(Request $request, Project $project): void
    {
        (self::isEditRoute($request) && (null === $this->editSlugParamExists($request)))
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::PROJECT_UNKNOWN));

        $project->isArchived()
            && throw new BadDataException(ProjectValidator::PROJECT_ALREADY_ARCHIVED);
    }

    public static function projectExists(?Project $project): bool
    {
        return (!$project?->isArchived()) && (null !== $project);
    }

    public static function getCategoriesArrayFromProject(Project $project): array
    {
        return array_map(
            static fn (Category $category) => [
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
            ],
            $project->getCategories()->toArray()
        );
    }
}
