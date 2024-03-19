<?php

namespace App\Service\User;

use App\Entity\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class UserFetchMapper
{
    /** @throws ExceptionInterface */
    public static function fromEntityToJson(User $user): mixed
    {
        $dto = self::fromEntityToDTO($user);
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->normalize($dto, JsonEncoder::FORMAT);
    }

    public static function fromEntityToDTO(User $user): UserFetchDTO
    {
        return new UserFetchDTO(
            $user->getSlug(),
            $user->getEmail(),
            $user->getUsername(),
            UserHelper::getProjectsArrayFromUser($user)
        );
    }
}