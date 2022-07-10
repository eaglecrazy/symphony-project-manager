<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\Model\User\UseCase\Email;
use App\Model\User\UseCase\Email\Request\Command as RequestCommand;
use App\Model\User\UseCase\Email\Confirm\Command as ConfirmCommand;
use App\Model\User\UseCase\Email\Request\Form;
use App\Model\User\UseCase\Email\Request\Handler as RequestHandler;
use App\Model\User\UseCase\Email\Confirm\Handler as ConfirmHandler;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("profile/email")
 */
class EmailController extends AbstractController
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("", name="profile.email")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Model\User\UseCase\Email\Request\Handler $handler
     * @return Response
     */
    public function request(Request $request, RequestHandler $handler): Response
    {
        $id = $this->getUser()->getId();

        $command = new RequestCommand($id);

        $form = $this->createForm(Form::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);

                $this->addFlash('success', 'Check your email');

                return $this->redirectToRoute('profile');
            } catch (DomainException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);

                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/profile/email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{token}", name="profile.email.confirm")
     * @param string $token
     * @param \App\Model\User\UseCase\Email\Confirm\Handler $handler
     * @return Response
     */
    public function confirm(string $token, ConfirmHandler $handler): Response
    {
        $id = $this->getUser()->getId();

        $command = new ConfirmCommand($id, $token);

        try {
            $handler->handle($command);

            $this->addFlash('success', 'Email is successfully changed.');
        } catch (DomainException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
