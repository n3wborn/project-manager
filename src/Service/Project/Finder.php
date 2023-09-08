<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\NotFoundException;
use App\Repository\ProjectRepository;

final class Finder
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function get(?Project $project): ?Project
    {
        (null === $project)
            && throw new NotFoundException();

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
}
