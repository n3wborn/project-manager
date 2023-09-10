<?php

namespace App\Service\Project;

use App\Entity\Project;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Mapper
{
    /** @throws ExceptionInterface */
    public static function fromEntityToJson(Project $project): mixed
    {
        $dto = self::fromEntityToDTO($project);
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->normalize($dto, JsonEncoder::FORMAT);
    }

    public static function fromEntityToDTO(Project $project): DTO
    {
        return new DTO(
            $project->getName(),
            $project->getDescription(),
            $project->getSlug(),
        );
    }
}
