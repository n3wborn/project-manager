<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;

final class Validator
{
    public const PROJECT_ALREADY_ARCHIVED = 'Le projet est déjà archivé';
    public const NAME_SHOULD_NOT_BE_EMPTY = 'Le champ Nom ne peut être vide';
    public const DESCRIPTION_SHOULD_NOT_BE_EMPTY = 'Le champ Description ne peut être vide';

    public function __construct()
    {
    }

    /** @throws BadDataException*/
    public function validate(DTO $dto): void
    {
        $this
            ->validateNameNotEmpty($dto->getName())
            ->validateDescriptionNotEmpty($dto->getDescription());
    }

    /** @throws BadDataException */
    private function validateDescriptionNotEmpty(string $description): self
    {
        empty($description) && throw new BadDataException(self::DESCRIPTION_SHOULD_NOT_BE_EMPTY);

        return $this;
    }

    /** @throws BadDataException */
    private function validateNameNotEmpty(string $name): self
    {
        empty($name) && throw new BadDataException(self::NAME_SHOULD_NOT_BE_EMPTY);

        return $this;
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
        && throw new NotFoundException(ApiMessages::translate(ApiMessages::PROJECT_UNKNOWN));

        return $this;
    }
}
