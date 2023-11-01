<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\BadDataException;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;

final class UserValidator
{
    public const USER_ALREADY_ARCHIVED = 'Cet utilisateur est inexistant ou a déja été supprimé';
    public const EMAIL_SHOULD_BE_UNIQUE = 'Cette addresse email est déjà utilisé';
    public const EMAIL_SHOULD_NOT_BE_EMPTY = 'Le champ email ne peut être vide';
    public const PROJECT_SLUG_INVALID = 'Au moins un des projets est invalide';

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
    ) {
    }

    /** @throws BadDataException */
    public function validate(UserDTO $dto, bool $isEditRoute = true): void
    {
        $this
            ->validateEmail($dto, $isEditRoute)
            ->validateProjects($dto)
        ;
    }

    /** @throws BadDataException */
    private function validateProjects(UserDTO $dto): self
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
        (null === $this->ProjectRepository->findOneBy(['slug' => $slug]))
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
    private function validateEmailNotEmpty(string $name): self
    {
        empty($name) && throw new BadDataException(self::EMAIL_SHOULD_NOT_BE_EMPTY);

        return $this;
    }

    /** @throws BadDataException */
    private function validateEmail(UserDTO $dto, bool $isEditRoute = true): self
    {
        $this->validateEmailNotEmpty($dto->getEmail());

        $isEditRoute
            ? $this->validateEditionEmail($dto)
            : $this->validateCreationEmail($dto);

        return $this;
    }

    /** @throws BadDataException */
    public function validateCreationEmail(UserDTO $dto): self
    {
        (null !== $this->userRepository->findNotArchivedByEmail($dto->getEmail()))
            && throw new BadDataException(self::EMAIL_SHOULD_BE_UNIQUE);

        return $this;
    }

    /** @throws BadDataException */
    public function validateEditionEmail(UserDTO $dto): self
    {
        $existingUser = $this->userRepository->findNotArchivedByEmail($dto->getEmail());

        (null !== $existingUser)
            && ($existingUser->getSlug() !== $dto->getSlug())
            && throw new BadDataException(self::EMAIL_SHOULD_BE_UNIQUE);

        return $this;
    }

    /** @throws BadDataException|NotFoundException */
    public function validateUserIsArchivable(?User $user): self
    {
        return $this
            ->validateKnownEntity($user)
            ->isArchivable($user);
    }

    /** @throws BadDataException */
    private function isArchivable(User $user): self
    {
        $user->isArchived()
        && throw new BadDataException(self::USER_ALREADY_ARCHIVED);

        return $this;
    }

    /** @throws NotFoundException */
    private function validateKnownEntity(?User $user): self
    {
        !$user
        && throw new NotFoundException(ApiMessages::translate(ApiMessages::USER_UNKNOWN));

        return $this;
    }
}
