<?php
namespace App\Controller\Visitor;




use DateTimeImmutable;
use App\Entity\Contact;
use App\Form\ContacFormType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods:['GET','POST'])]
public function contact(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
{
    // Création d'une nouvelle instance de l'entité Contact
    $contact = new Contact();

    // Création du formulaire de contact
    $form = $this->createForm(ContacFormType::class, $contact);

    // Traitement de la requête par le formulaire
    $form->handleRequest($request);
    
    // Vérification si le formulaire est soumis et valide
    if($form->isSubmitted() && $form->isValid())
    {
        // Récupération des données du formulaire
        $data = $form->getData();
        
        // Définition de la date de création du contact
        $contact->setCreatedAt(new DateTimeImmutable());

        // Création de l'email avec TemplatedEmail
        $email = (new TemplatedEmail())
            ->from($contact->getEmail()) // Utilisation de l'email du contact comme expéditeur
            ->to('j-leroy@gmail.fr')
            ->subject('Vous avez un nouveau message')
            ->htmlTemplate('email/contact.html.twig')
            ->context([
                // Passage des données du contact au template
                "contact_first_name" => $contact->getFirstName(),
                "contact_last_name" => $contact->getLastName(),
                "contact_email" => $contact->getEmail(),
                "contact_phone" => $contact->getPhone(),
                "message" => $contact->getMessage(), // Correction de la flèche en point
            ]);

        // Envoi de l'email
        $mailer->send($email);

        // Persistance du contact dans la base de données (décommentez si nécessaire)
        $em->persist($contact);
        $em->flush();

        // Ajout d'un message flash de succès
        $this->addFlash("success", "Votre message a bien été envoyé, nous vous répondrons dans les plus brefs délais");
        
        // Redirection vers la page de contact
        return $this->redirectToRoute('app_contact');
    }

    // Rendu de la page de contact avec le formulaire
    return $this->render('pages/visitor/contact.html.twig', ["form" => $form->createView()]);
}
}