<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Security;

use Gsu\SyllabusPortal\Entity\User;
use Gsu\SyllabusPortal\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/** @implements UserProviderInterface<User> */
class AccessTokenHandler implements AccessTokenHandlerInterface, UserProviderInterface
{
    /** @var string */
    protected const USER_KEY = '/users/identifiers/%s';


    /**
     * @param UserRepository $userRepository
     */
    public function __construct(private UserRepository $userRepository)
    {
    }


    /**
     * @param string $accessToken
     * @return UserBadge
     */
    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        try {
            return new UserBadge(
                $accessToken,
                fn (): User => $this->userRepository->getUser($accessToken) ?? throw new AccessDeniedException(),
            );
        } catch (\Throwable $t) {
            throw new AccessDeniedException('Invalid JWT', $t);
        }
    }


    /**
     * @param string $identifier
     * @return User
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->userRepository->getUser($identifier) ?? throw new UserNotFoundException();
    }


    /**
     * @param UserInterface $user
     * @return User
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return ($user instanceof User) ? $user : throw new UnsupportedUserException();
    }


    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
