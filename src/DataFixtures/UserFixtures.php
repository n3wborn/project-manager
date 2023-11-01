<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    public const QUANTITY = 10;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $count = rand(1, 5);

        for ($i = 1; $i <= self::QUANTITY; ++$i) {
            $user = $this->createUser($i);

            for ($j = 1; $j <= $count; ++$j) {
                /** @var Project $randProject */
                $randProject = $this->projectRepository->findOneByName('project-'.rand(1, ProjectFixtures::QUANTITY));
                $user->addProject($randProject);
                $randProject->setUserProject($user);
                $manager->persist($user);
                $manager->persist($randProject);
            }
        }

        $manager->persist($this->createAdmin());

        $manager->flush();
    }

    private function createUser(int $number): User
    {
        return ($user = new User())
            ->setEmail("mail$number@mail.com")
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'dev'));
    }

    private function createAdmin(): User
    {
        return ($user = new User())
            ->setEmail('admin@mail.com')
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'dev'));
    }

    public function getOrder(): int
    {
        return 3;
    }
}
