<?php

namespace App\Controller\Auth\OAuth;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GithubController extends AbstractController
{
    /**
     * @Route("/oauth/github", name="oauth.github")
     * @param ClientRegistry $clientRegistry
     * @return Response
     */
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('github_main')
            ->redirect(['public_profile']);
    }

    /**
     * @Route("/oauth/github/check", name="oauth.github_check")
     * @return Response
     */
    public function check(): Response
    {
        return $this->redirectToRoute('home');
    }
}
