<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;

final class Validator
{
    public const PROJECT_ALREADY_ARCHIVED = 'Le projet est déjà archivé';

    public function __construct()
    {
    }

    /** @throws BadDataException|NotFoundException */
    public function validateProjectIsArchivable(?Project $project): self
    {
        return $this
            ->validateKnownEntity($project)
            ->isArchivable($project);
    }

    /** @throws BadDataException */
    private function isArchivable(Project $project): self
    {
        $project->isArchived()
        && throw new BadDataException(self::PROJECT_ALREADY_ARCHIVED);

        return $this;
    }

    /** @throws NotFoundException */
    private function validateKnownEntity(?Project $project): self
    {
        !$project
        && throw new NotFoundException(ApiMessages::PROJECT_UNKNOWN);

        return $this;
    }
}
