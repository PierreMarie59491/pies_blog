<?php

// App\Security\AppCustomAuthenticator.php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Utilisation de l'interface

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    // Injection de UserPasswordHasherInterface
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserPasswordHasherInterface $passwordHasher  // Utilisation de l'interface ici
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->get('email');
        $password = $request->get('password');

        // Stocke l'email pour qu'il soit pré-rempli après une erreur
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),  // Utilisation de l'email pour retrouver l'utilisateur
            new PasswordCredentials($password),  // Passer le mot de passe brut
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')), // CSRF token
                new RememberMeBadge(),  // Gestion du "se souvenir de moi"
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Si une redirection existe, redirige l'utilisateur
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Sinon, redirige vers la page d'accueil (ou une autre route)
        return new RedirectResponse($this->urlGenerator->generate('home'));  // Remplace 'home' par ta route par défaut
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
