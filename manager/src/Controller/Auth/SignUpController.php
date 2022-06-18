<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Model\User\UseCase\SignUp;
use App\Model\User\UseCase\SignUp\Confirm\ByToken\Command as ConfirmCommand;
use App\Model\User\UseCase\SignUp\Confirm\ByToken\Handler as ConfirmHandler;
use App\Model\User\UseCase\SignUp\Request\Command as RequestCommand;
use App\Model\User\UseCase\SignUp\Request\Form;
use App\Model\User\UseCase\SignUp\Request\Handler as RequestHandler;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignUpController extends AbstractController
{
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @Route("/signup", name="auth.signup")
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function request(Request $request, RequestHandler $handler): Response
    {
        $command = new RequestCommand();

        $form = $this->createForm(Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Проверьте ваш email.');

                return $this->redirectToRoute('home');
            } catch (DomainException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $this->translator->trans($e->getMessage(), [], 'exceptions'));
            }
        }

        return $this->render('app/auth/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @route("/signup/{token}", name="auth.signup.confirm")
     * @param string $token
     * @param ConfirmHandler $handler
     * @return Response
     */
    public function confirm(string $token, ConfirmHandler $handler): Response {
        $command = new ConfirmCommand($token);

        try {
            $handler->handle($command);
            $this->addFlash('success', 'Email подтверждён успешно.');
            return $this->redirectToRoute('home');
        } catch (DomainException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }
}