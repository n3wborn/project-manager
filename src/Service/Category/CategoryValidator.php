<?php

namespace App\Service\Category;

use App\Entity\Category;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\CategoryRepository;

final class CategoryValidator
{
    public const CATEGORY_ALREADY_ARCHIVED = 'La catégorie est inexistante ou a déja été supprimée';
    public const NAME_SHOULD_BE_UNIQUE = 'La catégorie doit avoir un nom unique';
    public const NAME_SHOULD_NOT_BE_EMPTY = 'Le champ Nom ne peut être vide';

    public function __construct(
        private CategoryRepository $categoryRepository,
    ) {
    }

    /** @throws BadDataException*/
    public function validate(CategoryDTO $dto, bool $isEditRoute = true): void
    {
        $this
            ->validateNameNotEmpty($dto->getName())
            ->validateNameDoesNotExist($dto, $isEditRoute);
    }

    /** @throws BadDataException */
    public function validateNameDoesNotExist(CategoryDTO $dto, bool $onEdit = true): self
    {
        ($CategoryByName = $this->categoryRepository->findOneBy(['name' => $dto->getName(), 'archivedAt' => null])) instanceof Category
        && (
            (
                $onEdit
                && ($CategoryBySlug = $this->categoryRepository->findOneBySlug($dto->getSlug())) instanceof Category
                && $CategoryByName->getId() !== $CategoryBySlug->getId()
            ) || !$onEdit
        ) && throw new BadDataException(self::NAME_SHOULD_BE_UNIQUE);

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
