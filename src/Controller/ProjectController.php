<?php

namespace App\Controller;

use App\Entity\Project;
use App\Service\Project\Handler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectController extends AbstractController
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public const ROUTE_ADD = 'project_add';
    public const ROUTE_ARCHIVE = 'project_archive';
    public const ROUTE_EDIT = 'project_edit';
    public const ROUTE_FETCH = 'project_fetch';
    public const ROUTE_FIND_ALL = 'project_find_all';

    #[Route('/project/${project}', name: self::ROUTE_FETCH, methods: Request::METHOD_GET)]
    public function getProject(Project $project): JsonResponse
    {
        return $this->handler->handleGetProject($project);
    }

    #[Route('/project', name: self::ROUTE_ADD, methods: Request::METHOD_POST)]
    #[Route('/project/${project}', name: self::ROUTE_EDIT, methods: Request::METHOD_POST)]
    public function persistProject(?Project $project, Request $request): JsonResponse
    {
        return $this->handler->handlePersistProject($project, $request);
    }

    #[Route('/projects', name: self::ROUTE_FIND_ALL, methods: Request::METHOD_GET)]
    public function getAllProjects(): JsonResponse
    {
        return $this->handler->handleGetAllProjects();
    }
}
