<?php

namespace App\Model\User\Service;

use App\Model\User\Entity\User\Email;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;

class ConfirmTokenSender
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $form;

    /**
     * @param Swift_Mailer $mailer
     * @param Environment $twig
     * @param array $from
     */
    public function __construct(Swift_Mailer $mailer, Environment $twig, array $from)
    {
        $this->mailer = $mailer;
        $this->twig   = $twig;
        $this->form   = $from;
    }

    public function send(Email $email, string $token): void
    {
        $message = (new Swift_Message('Подтверждение email'))
            ->setFrom($this->form)
            ->setTo($email->getValue())
            ->setBody($this->twig->render('mail/user/signup.html.twig', [
                $token => $token,
            ]), 'text/html');

        if(!$this->mailer->send($message)) {
            throw new RuntimeException('Unable to send message.');
        }
    }
}
