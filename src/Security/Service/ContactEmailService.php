<?php
namespace App\Security\Service;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

    class ContactEmailService{
        private MailerInterface $mailer;
        public function __construct(MailerInterface $mailer){
            $this->mailer = $mailer;
        }
        public function send(array $data = []): void{
            $senderEmail =$data['sender_email'];
            $senderName =$data['sender_name'];
            $recipientEmail =$data['recipient_email'];
            $subject =$data['subject'];
            $htmlTemplate =$data['html_template'];
            $context =$data['context'];

            $email = new TemplatedEmail();

            $email  ->from(new Address($senderEmail, $senderName))
                    ->to($recipientEmail)
                    ->subject($subject)
                    ->htmlTemplate($htmlTemplate)
                    ->context($context)
                    ;
                    try{
                        $this->mailer->send($email);
                    }
                    catch (TransportExceptionInterface $te){
                        throw $te;
                    }

        }
    }