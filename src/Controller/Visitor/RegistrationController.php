<?php
namespace App\Controller\Visitor;



use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateTime;
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

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    { 
        
    }

    #[Route('/register', name: 'visitor_register', methods:['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encodage du mot de passe
            $passwordHashed = $userPasswordHasher->hashPassword($user,$form->get('password')->getData());
            // encode the plain password
            $user->setPassword($passwordHashed);
            $user->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('visitor_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('j-leroy@gmail.com', 'j-leroy'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre adresse email.')
                    ->htmlTemplate('pages/visitor/registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email
            // redirection du visiteur en attente de confirmation de l'email
            return $this->redirectToRoute('waiting_email');
        }

        return $this->render('pages/visitor/registration/register.html.twig', ['registrationForm' => $form,]);
    }
    #[Route('/visitor/waiting', name: 'waiting_email', methods:['GET'])]
    public function waitingForEmailVerification(): Response{
        return $this->render('pages/visitor/waiting_email.html.twig');

    }

    #[Route('/verify/email', name: 'visitor_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('visitor_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('visitor_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('visitor_register');
        }
        $user->setVerifiedAt (new DateTimeImmutable());
        $entityManager->persist($user);
        $entityManager->flush();
        
        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre email à bien été vérifiée.');

        return $this->redirectToRoute('app_welcome');
    }
}
