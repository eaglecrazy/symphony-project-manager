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
     * На этот роут редиректит гитхаб когда пользователь аутентифицируется там.
     * Впрочем, это не важно, так как после редиректа в GithubAuthenticator отработает метод
     * onAuthenticationSuccess и редирект будет в другое место.
     *
     * Хрен знает почему тут не работает dd() и отладка.
     * В App\Controller\Profile\OAuthGithubController она работает.
     *
     * @Route("/oauth/github/check", name="oauth.github_check")
     * @return Response
     */
    public function check(): Response
    {
        return $this->redirectToRoute('home');
    }
}
