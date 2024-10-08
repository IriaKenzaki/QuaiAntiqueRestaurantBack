<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const USER_NB_TUPLES =20;
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for($i =1; $i<= self::USER_NB_TUPLES; $i++){
            $user = (new User())
                ->setFirstName("Firstname $i")
                ->setLastName("Lastname $i")
                ->setGuestNumber(random_int(0, 10))
                ->setEmail("email.$i@studi.fr")
                ->setCreatedAt(new DateTimeImmutable());

            $user->setPassword($this->passwordHasher->hashPassword($user,"password$i"));

            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
       return ['independent', 'user'];
    }
}
