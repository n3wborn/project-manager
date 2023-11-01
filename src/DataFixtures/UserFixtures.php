<?php

namespace App\DataFixtures;

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
        $admin = $this->createAdmin();
        $manager->persist($admin);

        for ($i = 1; $i <= self::QUANTITY; ++$i) {
            $user = $this->createUser($i);
            $manager->persist($user);
        }

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
