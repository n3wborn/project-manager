<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ProjectFixtures extends Fixture implements OrderedFixtureInterface
{
    public const QUANTITY = 10;

    public function __construct(
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::QUANTITY; ++$i) {
            $count = rand(1, 5);
            $project = $this->createProject($i);
            $manager->persist($project);

            for ($j = 1; $j <= $count; ++$j) {
                $randCategory = $this->categoryRepository->findOneBy(['name' => 'category-'.rand(1, CategoryFixtures::CATEGORY_QUANTITY)]);
                $randCategory->addProject($project);
                $manager->persist($randCategory);
            }
        }

        $manager->flush();
    }

    private function createProject(int $number): Project
    {
        return (new Project())
            ->setName("project-$number")
            ->setDescription("project-description-$number");
    }

    public function getOrder(): int
    {
        return 2;
    }
}
