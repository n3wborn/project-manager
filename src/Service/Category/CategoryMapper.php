<?php

namespace App\Service\Category;

use App\Entity\Category;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class CategoryMapper
{
    /** @throws ExceptionInterface */
    public static function fromEntityToJson(Category $category): mixed
    {
        $dto = self::fromEntityToDTO($category);
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->normalize($dto, JsonEncoder::FORMAT);
    }

    public static function fromEntityToDTO(Category $category): CategoryDTO
    {
        return new CategoryDTO(
            $category->getName(),
            $category->getSlug(),
            CategoryHelper::getProjectsArrayFromCategory($category)
        );
    }
}
