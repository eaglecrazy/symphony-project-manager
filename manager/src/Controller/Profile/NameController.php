<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\Model\User\UseCase\Name\Command;
use App\Model\User\UseCase\Name\Form;
use App\Model\User\UseCase\Name\Handler;
use App\ReadModel\User\UserFetcher;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("profile/name")
 */
class NameController extends AbstractController
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \App\ReadModel\User\UserFetcher
     */
    private $users;

    public function __construct(UserFetcher $users, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->users  = $users;
    }

    /**
     * @Route("", name="profile.name")
     * @param Request $request
     * @param Handler $handler
     * @return Response
     */
    public function request(Request $request, Handler $handler): Response
    {
        $id = $this->getUser()->getId();

        $user = $this->users->getDetail($id);

        $command = new Command($user->id);

        $command->firstName = $user->first_name;
        $command->lastName  = $user->last_name;

        $form = $this->createForm(Form::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);

                return $this->redirectToRoute('profile');
            } catch (DomainException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);

                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/profile/name.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
