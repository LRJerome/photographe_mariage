<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher =$hasher;

    }
    public function load(ObjectManager $manager): void
    {
        $superAdmin = $this->createSuperAdminFixtures();

        $manager->persist($superAdmin);

        $manager->flush();
    }
    private function createSuperAdminFixtures(): User{
        $superAdmin = new User();

        $passwordHashed = $this->hasher->hashPassword($superAdmin, "azerty1234A*");

        $superAdmin ->setFirstName("Jerome");
        $superAdmin ->setLastName("LEROY");
        $superAdmin ->setEmail("j-leroy@gmail.fr");
        $superAdmin ->setPhoneNumber("0606060606");
        $superAdmin ->setAdress("12 rue de Brest 29000 Quimper");
        $superAdmin ->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);
        $superAdmin ->setPassword("$passwordHashed");
        $superAdmin ->setVerified(true);
        $superAdmin ->setCreatedAt( new DateTimeImmutable());
        $superAdmin ->setVerifiedAt( new DateTimeImmutable());
     
        return $superAdmin;
    }
}
