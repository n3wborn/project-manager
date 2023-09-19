<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\ProjectRepository;

final class ProjectFinder
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function get(Project $project): ?Project
    {
        $project->isArchived()
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::PROJECT_NOT_FOUND));

        return
            (
                null !== ($result = $this->projectRepository->find($project))
            )
            ? $result
            : null;
    }

    public function getAll(): array
    {
        return $this->projectRepository->findAll();
    }

    public function getAllNotArchived(): array
    {
        return $this->projectRepository->findAllNotArchived();
    }
}
