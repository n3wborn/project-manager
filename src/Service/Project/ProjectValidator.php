<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;

final class ProjectValidator
{
    public const DESCRIPTION_SHOULD_NOT_BE_EMPTY = 'Le champ Description ne peut être vide';
    public const NAME_SHOULD_BE_UNIQUE = 'Le projet doit avoir un nom unique';
    public const NAME_SHOULD_NOT_BE_EMPTY = 'Le champ Nom ne peut être vide';
    public const PROJECT_ALREADY_ARCHIVED = 'Le projet est inexistant ou a déja été supprimé';
    public const CATEGORY_SLUG_INVALID = 'Au moins une des catégories est invalide';

    public function __construct(
        private ProjectRepository $projectRepository,
        private CategoryRepository $categoryRepository,
    ) {
    }

    /** @throws BadDataException */
    public function validate(ProjectDTO $dto, bool $isEditRoute = true): void
    {
        $this
            ->validateName($dto, $isEditRoute)
            ->validateDescriptionNotEmpty($dto)
            ->validateCategories($dto)
        ;
    }

    /** @throws BadDataException */
    private function validateCategories(ProjectDTO $dto): self
    {
        foreach ($dto->getCategories() as $category) {
            $this->validateCategory($category);
        }

        return $this;
    }

    /** @throws BadDataException */
    private function validateCategory(mixed $data): self
    {
        $this
            ->validateCategoryFormat($data)
            ->validateCategoryIsNotEmptyString($data)
            ->validateCategorySlugIsValidEntity($data);

        return $this;
    }

    private function validateCategoryFormat(mixed $data): self
    {
        (!is_array($data) || !array_key_exists('slug', $data))
            && throw new BadDataException(self::CATEGORY_SLUG_INVALID);

        return $this;
    }

    private function validateCategoryIsNotEmptyString(mixed $categorySlug): self
    {
        (empty($categorySlug['slug']) || !is_string($categorySlug['slug']))
            && throw new BadDataException(self::CATEGORY_SLUG_INVALID);

        return $this;
    }

    /** @throws BadDataException */
    private function validateCategorySlugIsValidEntity(mixed $data): self
    {
        $slug = $data['slug'];

        $this
            ->validateCategoryExists($slug)
            ->validateCategoryIsNotArchived($slug);

        return $this;
    }

    /** @throws BadDataException */
    private function validateCategoryExists(string $slug): self
    {
        (null === $this->categoryRepository->findOneBy(['slug' => $slug]))
            && throw new NotFoundException(self::CATEGORY_SLUG_INVALID);

        return $this;
    }

    /** @throws BadDataException */
    private function validateCategoryIsNotArchived(string $slug): self
    {
        (null !== $this->categoryRepository->findOneBy(['slug' => $slug])->getArchivedAt())
            && throw new NotFoundException(self::CATEGORY_SLUG_INVALID);

        return $this;
    }

    /** @throws BadDataException */
    private function validateDescriptionNotEmpty(ProjectDTO $dto): self
    {
        empty($dto->getDescription()) && throw new BadDataException(self::DESCRIPTION_SHOULD_NOT_BE_EMPTY);

        return $this;
    }

    /** @throws BadDataException */
    private function validateNameNotEmpty(string $name): self
    {
        empty($name) && throw new BadDataException(self::NAME_SHOULD_NOT_BE_EMPTY);

        return $this;
    }

    /** @throws BadDataException */
    private function validateName(ProjectDTO $dto, bool $isEditRoute = true): self
    {
        $this->validateNameNotEmpty($dto->getName());

        $isEditRoute
            ? $this->validateEditionName($dto)
            : $this->validateCreationName($dto);

        return $this;
    }

    /** @throws BadDataException */
    public function validateCreationName(ProjectDTO $dto): self
    {
        (null !== $this->projectRepository->findNotArchivedByName($dto->getName()))
            && throw new BadDataException(self::NAME_SHOULD_BE_UNIQUE);

        return $this;
    }

    /** @throws BadDataException */
    public function validateEditionName(ProjectDTO $dto): self
    {
        $existingProject = $this->projectRepository->findNotArchivedByName($dto->getName());

        (null !== $existingProject)
            && ($existingProject->getSlug() !== $dto->getSlug())
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
