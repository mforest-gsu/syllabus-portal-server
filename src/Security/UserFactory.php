<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Security;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Gsu\SyllabusPortal\Entity\User;
use Gsu\SyllabusPortal\Entity\UserAuthCode;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UserFactory
{
    /**
     * @param string $tenantId
     * @param string $clientId
     * @param CachedKeySet $jwks
     * @param DenormalizerInterface $denormalizer
     * @param AuthStateFactory $authStateFactory
     */
    public function __construct(
        private string $tenantId,
        private string $clientId,
        private CachedKeySet $jwks,
        private DenormalizerInterface $denormalizer,
        private AuthStateFactory $authStateFactory
    ) {
    }


    /**
     * @param UserAuthCode $authCode
     * @return User
     */
    public function createUser(UserAuthCode $authCode): User
    {
        $user = $this->denormalize($authCode->getIdToken());

        $this->validate($user, $authCode->getState());

        return $this->transform($user);
    }


    /**
     * @param string $idToken
     * @return User
     */
    protected function denormalize(string $idToken): User
    {
        $leeway = JWT::$leeway;
        JWT::$leeway = 30;
        $values = (array) JWT::decode($idToken, $this->jwks);
        $values['userIdentifier'] = hash('SHA256', $idToken);
        $values['jwt'] = $idToken;
        $values['attributes'] = $values;
        JWT::$leeway = $leeway;

        $user = $this->denormalizer->denormalize(
            $values,
            User::class
        );

        return ($user instanceof User)
            ? $user
            : throw new \RuntimeException();
    }


    /**
     * @param User $user
     * @param string $state
     * @return void
     */
    protected function validate(
        User $user,
        string $state
    ): void {
        $nonce = $this->authStateFactory->validateState($state);
        if ($nonce !== $user->getNonce()) {
            throw new \RuntimeException();
        }

        if ($this->tenantId !== $user->getTid()) {
            throw new \RuntimeException();
        }

        if ($this->clientId !== $user->getAud()) {
            throw new \RuntimeException();
        }
    }


    /**
     * @param User $user
     * @return User
     */
    protected function transform(User $user): User
    {
        $roles = $user->getRoles();

        if (str_ends_with($user->getPreferredUsername(), '@student.gsu.edu')) {
            $roles[] = 'ROLE_STUDENT';
        } elseif (str_ends_with($user->getPreferredUsername(), '@gsu.edu')) {
            $roles[] = 'ROLE_STAFF';
        }

        return $user->setRoles($roles);
    }
}
