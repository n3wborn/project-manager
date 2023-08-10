<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ProjectFixtures extends Fixture
{
    public const QUANTITY = 10;

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::QUANTITY; ++$i) {
            $project = $this->createProject($i);
            $manager->persist($project);
        }

        $manager->flush();
    }

    private function createProject(int $number): Project
    {
        return (new Project())
            ->setName("project-$number")
            ->setDescription("project-description-$number");
    }
}
