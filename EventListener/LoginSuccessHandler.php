<?php

namespace App\AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface {

    protected $router;
    protected $security;

    public function __construct($router, AuthorizationChecker $security) {
        $this->router = $router;
        $this->security = $security;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        if ($this->security->isGranted('ROLE_ADMINISTRATOR') || $this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return new RedirectResponse($this->router->generate('admin'));
        }

        if ($request->headers->get('referer') !== null) {
            return new RedirectResponse($request->headers->get('referer'));
        }

        return new RedirectResponse($this->router->generate('homepage'));
    }

}
