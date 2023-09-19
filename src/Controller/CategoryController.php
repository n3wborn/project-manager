<?php

namespace App\Controller;

use App\Entity\Category;
use App\Service\Category\CategoryDTO;
use App\Service\Category\CategoryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryHandler $handler,
    ) {
    }

    public const ROUTE_ADD = 'category_add';
    public const ROUTE_ARCHIVE = 'category_archive';
    public const ROUTE_EDIT = 'category_edit';
    public const ROUTE_FETCH = 'category_fetch';
    public const ROUTE_FIND_ALL = 'category_find_all';
    public const ROUTE_GET_PROJECTS = 'category_get_projects';

    #[Route('/category/{slug}', name: self::ROUTE_FETCH, methods: Request::METHOD_GET)]
    public function getcategory(?Category $category): JsonResponse
    {
        return $this->handler->handleGetCategory($category);
    }

    #[Route('/category', name: self::ROUTE_ADD, methods: Request::METHOD_POST)]
    #[Route('/category/{slug}', name: self::ROUTE_EDIT, methods: Request::METHOD_POST)]
    public function persistCategory(
        ?Category $category,
        #[MapRequestPayload()] ?CategoryDTO $dto,
        Request $request,
    ): JsonResponse {
        return $this->handler->handlePersistCategory($category, $request, $dto);
    }

    #[Route('/categories', name: self::ROUTE_FIND_ALL, methods: Request::METHOD_GET)]
    public function getAllCategories(): JsonResponse
    {
        return $this->handler->handleGetAllCategories();
    }

    #[Route('/category/{slug}', name: self::ROUTE_ARCHIVE, methods: 'DELETE')]
    public function archiveCategory(?Category $category): JsonResponse
    {
        return $this->handler->handleArchiveCategory($category);
    }

    #[Route('/category/{slug}/projects', name: self::ROUTE_GET_PROJECTS, methods: Request::METHOD_GET)]
    public function getCategoryProjects(?Category $category): JsonResponse
    {
        return $this->handler->handleGetCategoryProjects($category);
    }
}
