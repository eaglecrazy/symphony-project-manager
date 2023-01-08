<?php

namespace App\Controller\Profile\OAuth;

use App\Model\User\UseCase\Network\Detach\Command;
use App\Model\User\UseCase\Network\Detach\Handler;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route "/profile/oauth"
 */
class DetachController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/detach/{network}/{identity}", name="profile.oauth.detach", methods={"DELETE"})
     * @param Request $request
     * @param string $network
     * @param string $identity
     * @param Handler $handler
     * @return Response
     */
    public function connect(Request $request, string $network, string $identity, Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('profile');
        }

        $command = new Command($this->getUser()->getId(), $network, $identity);

        try {
            $handler->handle($command);
        } catch (DomainException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
