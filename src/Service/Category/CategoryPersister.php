<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Helper\ApiResponse;
use App\Helper\ExceptionLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CategoryPersister
{
    public function __construct(
        private CategoryHelper $helper,
        private ExceptionLogger $logger,
        private CategoryValidator $validator,
        private EntityManagerInterface $em,
    ) {
    }

    public function processRequest(Category $category, CategoryDTO $dto, Request $request): JsonResponse
    {
        try {
            $this->helper->validateRequestResource($request, $category);
            $this->validator->validate($dto, $this->helper->isEditRoute($request));
            $this->persist($category, $dto);

            $response = ApiResponse::createAndFormat(
                CategoryMapper::fromEntityToJson($category),
                CategoryHelper::generateEditSuccessMessage($request)
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

    /** @throws NotFoundException */
    public function persist(Category $category, CategoryDTO $dto): void
    {
        $category->setName($dto->getName());

        /** @var Project $project */
        foreach ($category->getProjects() as $project) {
            $category->removeProject($project);
            $project->removeCategory($category);
        }

        foreach ($dto->getProjects() as $dtoProject) {
            /** @var Project $project */
            $project = $this->em->getRepository(Project::class)->findOneBySlug($dtoProject['slug']);
            $category->addProject($project);
            $project->addCategory($category);
        }

        $this->em->persist($category);
        $this->em->flush();
    }
}
