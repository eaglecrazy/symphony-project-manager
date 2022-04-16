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
     * @param Swift_Mailer $mailer
     * @param Environment $twig
     */
    public function __construct(Swift_Mailer $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig   = $twig;
    }

    public function send(Email $email, string $token): void
    {
        $message = (new Swift_Message('Подтверждение email'))
            ->setTo($email->getValue())
            ->setBody($this->twig->render('mail/user/signup.html.twig', [
                'token' => $token,
            ]), 'text/html');

        if (!$this->mailer->send($message)) {
            throw new RuntimeException('Unable to send message.');
        }
    }
}
