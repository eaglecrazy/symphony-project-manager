<?php

namespace App\Controller\Profile\OAuth;

use DomainException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\User\UseCase\Network\Attach\Handler;
use App\Model\User\UseCase\Network\Attach\Command;

/**
 * @Route "/profile/oauth/github"
 */
class GithubController extends AbstractController
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
     *
     * @Route("/attach", name="profile.oauth.github")
     * @param ClientRegistry $clientRegistry
     * @return Response
     */
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('github_attach')
            ->redirect(['public_profile']);
    }

    /**
     * На этот роут редиректит гитхаб когда пользователь аутентифицируется там.
     *
     * @Route("/check", name="profile.oauth.github_check")
     * @param \KnpU\OAuth2ClientBundle\Client\ClientRegistry $clientRegistry
     * @param \App\Model\User\UseCase\Network\Attach\Handler $handler
     * @return Response
     */
    public function check(ClientRegistry $clientRegistry, Handler $handler): Response
    {
        $client = $clientRegistry->getClient('github_attach');

        $command = new Command(
            $this->getUser()->getId(),
            'github',
            $client->fetchUser()->getId()
        );

        try {
            $handler->handle($command);

            $this->addFlash('success', 'Github successfully attached.');
        } catch (DomainException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
