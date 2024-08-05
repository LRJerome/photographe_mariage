<?php

namespace App\Controller\Visitor;

// Importation des classes nécessaires
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

// Définition de mon premier controller pour l'inscription des utilisateurs
class RegistrationController extends AbstractController
{
    // Le constructeur injecte le service EmailVerifier
    public function __construct(private EmailVerifier $emailVerifier)
    { 
        
    }

    // Route pour l'inscription, accessible via GET et POST
    #[Route('/register', name: 'visitor_register', methods:['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Création d'un nouvel utilisateur et du formulaire associé
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $passwordHashed = $userPasswordHasher->hashPassword($user,$form->get('password')->getData());
            $user->setPassword($passwordHashed);
            $user->setCreatedAt(new DateTimeImmutable());

            // Enregistrement de l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi d'un email de confirmation
            $this->emailVerifier->sendEmailConfirmation('visitor_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('j-leroy@gmail.com', 'j-leroy'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre adresse email.')
                    ->htmlTemplate('pages/visitor/registration/confirmation_email.html.twig')
            );

            // Redirection vers une page d'attente
            return $this->redirectToRoute('waiting_email');
        }

        // Affichage du formulaire d'inscription
        return $this->render('pages/visitor/registration/register.html.twig', ['registrationForm' => $form,]);
    }

    // Route pour la page d'attente de confirmation d'email
    #[Route('/visitor/waiting', name: 'waiting_email', methods:['GET'])]
    public function waitingForEmailVerification(): Response{
        return $this->render('pages/visitor/waiting_email.html.twig');
    }

    // Route pour la vérification de l'email
    #[Route('/verify/email', name: 'visitor_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'ID de l'utilisateur depuis la requête
        $id = $request->query->get('id');

        // Vérifications de sécurité
        if (null === $id) {
            return $this->redirectToRoute('visitor_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('visitor_register');
        }

        // Validation du lien de confirmation d'email
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('visitor_register');
        }

        // Mise à jour de la date de vérification de l'email
        $user->setVerifiedAt(new DateTimeImmutable());
        $entityManager->persist($user);
        $entityManager->flush();
        
        // Ajout d'un message flash de succès
        $this->addFlash('success', 'Votre email à bien été vérifiée.');

        // Redirection vers la page d'accueil
        return $this->redirectToRoute('app_welcome');
    }
}