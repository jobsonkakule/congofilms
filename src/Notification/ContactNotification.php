<?php
namespace App\Notification;

use App\Entity\Contact;
use Twig\Environment;

class ContactNotification 
{
    
    private $mailFrom = 'no-reply@grandslacsnews.com';
    private $mailer;

    private $renderer;

    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    public function notify(Contact $contact) {
        $message = (new \Swift_Message('Quelqu\'un vous a contacté | Grands Lacs News '))
            ->setFrom($this->mailFrom)
            ->setTo('contact@grandslacsnews.com')
            ->setReplyTo($contact->getEmail())
            ->setBody($this->renderer->render('emails/contact.html.twig', [
                'contact' => $contact
            ]), 'text/html');
        $this->mailer->send($message);
    }

    public function sendResetLink(string $to, $params)
    {
        $message = (new \Swift_Message('Réinitialisation du mot de passe | Grands Lacs News'))
            ->setFrom($this->mailFrom)
            ->setTo($to)
            ->setReplyTo('jobkakule10@outlook.com')
            ->setBody($this->renderer->render('emails/password.html.twig', [
                'params' => $params
            ]), 'text/html');
        $this->mailer->send($message);
    }
}