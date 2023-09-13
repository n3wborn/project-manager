<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class CategoryFixtures extends Fixture implements OrderedFixtureInterface
{
    public const CATEGORY_QUANTITY = 20;

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::CATEGORY_QUANTITY; ++$i) {
            $category = (new Category())->setName("category-$i");
            $manager->persist($category);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
