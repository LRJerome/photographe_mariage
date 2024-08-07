<?php

// Ce fichier DataFixtures sert à créer des données initiales dans la base de données.
// Ici, on crée un super administrateur par défaut pour notre application.
// C'est très utile pour avoir un compte admin dès le démarrage du projet.

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{
    // On déclare une propriété privée pour le hacheur de mot de passe
    private UserPasswordHasherInterface $hasher;

    // Le constructeur injecte le service de hachage de mot de passe
    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }

    // Cette méthode est appelée quand on exécute les fixtures
    public function load(ObjectManager $manager): void
    {
        // On crée le super admin
        $superAdmin = $this->createSuperAdminFixtures();

        // On dit à Doctrine de le persister (préparer à l'enregistrement)
        $manager->persist($superAdmin);

        // On envoie tout en base de données
        $manager->flush();
    }

    // Méthode privée pour créer le super admin
    private function createSuperAdminFixtures(): User{
        // On crée une nouvelle instance de User
        $superAdmin = new User();

        // On hache le mot de passe (important pour la sécurité!)
        $passwordHashed = $this->hasher->hashPassword($superAdmin, "azerty1234A*");

        // On définit toutes les propriétés du super admin
        $superAdmin->setFirstName("Jerome");
        $superAdmin->setLastName("LEROY");
        $superAdmin->setEmail("j-leroy@gmail.fr");
        $superAdmin->setPhoneNumber("0606060606");
        $superAdmin->setAdress("12 rue de Brest 29000 Quimper");
        // On lui donne tous les rôles (super admin, admin et user)
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);
        // On assigne le mot de passe haché
        $superAdmin->setPassword($passwordHashed);
        // On marque le compte comme vérifié
        $superAdmin->setVerified(true);
        // On définit la date de création et de vérification à maintenant
        $superAdmin->setCreatedAt(new DateTimeImmutable());
        $superAdmin->setVerifiedAt(new DateTimeImmutable());
    
        // On retourne l'objet super admin créé
        return $superAdmin;
    }
}
