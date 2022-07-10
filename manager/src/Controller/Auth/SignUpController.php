<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Model\User\UseCase\SignUp;
use App\Model\User\UseCase\SignUp\Confirm\ByToken\Command as ConfirmByTokenCommand;
use App\Model\User\UseCase\SignUp\Confirm\ByToken\Handler as ConfirmHandler;
use App\Model\User\UseCase\SignUp\Request\Command as RequestCommand;
use App\Model\User\UseCase\SignUp\Request\Form;
use App\Model\User\UseCase\SignUp\Request\Handler as RequestHandler;
use App\ReadModel\User\UserFetcher;
use App\Security\LoginFormAuthenticator;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignUpController extends AbstractController
{
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \App\ReadModel\User\UserFetcher
     */
    private $users;

    public function __construct(
        LoggerInterface $logger,
        UserFetcher $users,
        TranslatorInterface $translator
    ) {
        $this->logger     = $logger;
        $this->translator = $translator;
        $this->users      = $users;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $token
     * @param ConfirmHandler $handler
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     * @param \Symfony\Component\Security\Guard\GuardAuthenticatorHandler $guardHandler
     * @param \App\Security\LoginFormAuthenticator $authenticator
     * @return Response
     */
    public function confirm(
        Request $request,
        string $token,
        ConfirmHandler $handler,
        UserProviderInterface $userProvider,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ): Response {
        $user = $this->users->findBySignupConfirmToken($token);

        if (!$user) {
            $this->addFlash('error', 'Incorrect or already confirmed token.');

            return $this->redirectToRoute('auth.signup');
        }

        $command = new ConfirmByTokenCommand($token);

        try {
            $handler->handle($command);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $userProvider->loadUserByUsername($user->email),
                $request,
                $authenticator,
                'main'
            );
        } catch (DomainException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('auth.signup');
        }
    }
}