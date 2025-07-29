<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Controller;

use Gsu\SyllabusPortal\Entity\UserAuthCode;
use Gsu\SyllabusPortal\Repository\UserRepository;
use Gsu\SyllabusPortal\Security\AuthStateFactory;
use Gsu\SyllabusPortal\Security\UserFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private AuthStateFactory $authStateFactory,
        private UserFactory $userFactory,
        private UserRepository $userRepository
    ) {
    }


    #[Route(
        name: 'auth.login',
        methods: 'GET',
        path: '/auth/login'
    )]
    public function login(): RedirectResponse
    {
        $redirectUri = $this->generateUrl(
            route: 'auth.callback',
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        $authLoginUri = $this->authStateFactory->createLoginUri($redirectUri);

        return new RedirectResponse($authLoginUri);
    }


    #[Route(
        name: 'auth.callback',
        methods: 'POST',
        path: '/auth/callback'
    )]
    public function callback(
        #[MapRequestPayload] UserAuthCode $authCode
    ): RedirectResponse {
        $user = $this->userFactory->createUser($authCode);

        $this->userRepository->setUser($user);

        $webUri = $this->authStateFactory->createWebUri($user);

        return new RedirectResponse($webUri);
    }


    #[Route(
        name: 'auth.check',
        methods: 'GET',
        path: '/auth/check',
        format: 'json'
    )]
    public function check(Request $request): Response
    {
        $accessToken = substr(
            $request->headers->get('Authorization') ?? '',
            strlen('Bearer ')
        );

        $user = $this->userRepository->getUser($accessToken);

        return new JsonResponse($user !== null);
    }
}
