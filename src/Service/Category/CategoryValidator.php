<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;

final class CategoryValidator
{
    public const CATEGORY_ALREADY_ARCHIVED = 'La catégorie est inexistante ou a déja été supprimée';
    public const NAME_SHOULD_BE_UNIQUE = 'La catégorie doit avoir un nom unique';
    public const NAME_SHOULD_NOT_BE_EMPTY = 'Le champ Nom ne peut être vide';
    public const PROJECT_SLUG_INVALID = 'Au moins un des projets est invalide';

    public function __construct(
        private CategoryRepository $categoryRepository,
        private ProjectRepository $projectRepository,
    ) {
    }

    /** @throws BadDataException */
    public function validate(CategoryDTO $dto, bool $isEditRoute = true): void
    {
        $this
            ->validateNameNotEmpty($dto->getName())
            ->validateProjects($dto)
        ;

        $isEditRoute
            ? $this->validateEditionName($dto)
            : $this->validateCreationName($dto);
    }

    /** @throws BadDataException */
    private function validateProjects(CategoryDTO $dto): self
    {
        foreach ($dto->getProjects() as $project) {
            $this->validateProject($project);
        }

        return $this;
    }

    /** @throws BadDataException */
    private function validateProject(mixed $data): self
    {
        $this
            ->validateProjectFormat($data)
            ->validateProjectIsNotEmptyString($data)
            ->validateProjectSlugIsValidEntity($data);

        return $this;
    }

    private function validateProjectFormat(mixed $data): self
    {
        (!is_array($data) || !array_key_exists('slug', $data))
            && throw new BadDataException(self::PROJECT_SLUG_INVALID);

        return $this;
    }

    private function validateProjectIsNotEmptyString(mixed $projectSlug): self
    {
        (empty($projectSlug['slug']) || !is_string($projectSlug['slug']))
            && throw new BadDataException(self::PROJECT_SLUG_INVALID);

        return $this;
    }

    /** @throws BadDataException */
    private function validateProjectSlugIsValidEntity(mixed $data): self
    {
        $slug = $data['slug'];

        $this
            ->validateProjectExists($slug)
            ->validateProjectIsNotArchived($slug);

        return $this;
    }

    /** @throws BadDataException */
    private function validateProjectExists(string $slug): self
    {
        (null === $this->projectRepository->findOneBy(['slug' => $slug]))
            && throw new NotFoundException(self::PROJECT_SLUG_INVALID);

        return $this;
    }

    /** @throws BadDataException */
    private function validateProjectIsNotArchived(string $slug): self
    {
        (null !== $this->projectRepository->findOneBy(['slug' => $slug])->getArchivedAt())
            && throw new NotFoundException(self::PROJECT_SLUG_INVALID);

        return $this;
    }

    /** @throws BadDataException */
    public function validateCreationName(CategoryDTO $dto): self
    {
        (null !== $this->categoryRepository->findNotArchivedByName($dto->getName()))
            && throw new BadDataException(self::NAME_SHOULD_BE_UNIQUE);

        return $this;
    }

    /** @throws BadDataException */
    public function validateEditionName(CategoryDTO $dto): self
    {
        $existingCategory = $this->categoryRepository->findNotArchivedByName($dto->getName());

        (null !== $existingCategory)
            && ($existingCategory->getSlug() !== $dto->getSlug())
            && throw new BadDataException(self::NAME_SHOULD_BE_UNIQUE);

        return $this;
    }

    /** @throws BadDataException */
    private function validateNameNotEmpty(string $name): self
    {
        empty($name) && throw new BadDataException(self::NAME_SHOULD_NOT_BE_EMPTY);

        return $this;
    }

    /** @throws BadDataException|NotFoundException */
    public function validateCategoryIsArchivable(?Category $category): self
    {
        return $this
            ->validateKnownEntity($category)
            ->isArchivable($category);
    }

    /** @throws BadDataException */
    private function isArchivable(Category $category): self
    {
        $category->isArchived() && throw new BadDataException(self::CATEGORY_ALREADY_ARCHIVED);

        return $this;
    }

    /** @throws NotFoundException */
    private function validateKnownEntity(?Category $category): self
    {
        !$category && throw new NotFoundException(ApiMessages::translate(ApiMessages::CATEGORY_UNKNOWN));

        return $this;
    }
}
