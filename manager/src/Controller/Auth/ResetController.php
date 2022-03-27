<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Model\User\UseCase\Reset;
use App\Model\User\UseCase\Reset\Request\Command as RequestCommand;
use App\Model\User\UseCase\Reset\Request\Form as RequestForm;
use App\Model\User\UseCase\Reset\Request\Handler as RequestHandler;
use App\Model\User\UseCase\Reset\Reset\Command as ResetCommand;
use App\Model\User\UseCase\Reset\Reset\Form as ResetForm;
use App\Model\User\UseCase\Reset\Reset\Handler as ResetHandler;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/reset", name="auth.reset")
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function request(Request $request, RequestHandler $handler): Response
    {
        $command = new RequestCommand();

        $form = $this->createForm(RequestForm::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Проверьте ваш email.');

                return $this->redirectToRoute('home');
            } catch (DomainException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auth/reset/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @route("/reset/{token}", name="auth.reset.reset")
     * @param string $token
     * @param Request $request
     * @param ResetHandler $handler
     * @return Response
     */
    public function reset(string $token, Request $request, ResetHandler $handler): Response
    {
        $command = new ResetCommand($token);

        $form = $this->createForm(ResetForm::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Password is successfully changed.');

                return $this->redirectToRoute('home');
            } catch (DomainException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auth/reset/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}