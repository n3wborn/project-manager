<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\ProjectRepository;

final class ProjectValidator
{
    public const DESCRIPTION_SHOULD_NOT_BE_EMPTY = 'Le champ Description ne peut être vide';
    public const NAME_SHOULD_BE_UNIQUE = 'Le projet doit avoir un nom unique';
    public const NAME_SHOULD_NOT_BE_EMPTY = 'Le champ Nom ne peut être vide';
    public const PROJECT_ALREADY_ARCHIVED = 'Le projet a déja été supprimé';

    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    /** @throws BadDataException*/
    public function validate(ProjectDTO $dto): void
    {
        $this
            ->validateName($dto->getName())
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

    /** @throws BadDataException */
    private function validateName(string $name): self
    {
        return $this
            ->validateNameNotEmpty($name)
            ->validateNameIsUnique($name);
    }

    /** @throws BadDataException */
    private function validateNameIsUnique(string $name): self
    {
        $this->projectRepository->findBy(['name' => $name, 'archivedAt' => null])
            && throw new BadDataException(self::NAME_SHOULD_BE_UNIQUE);

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
