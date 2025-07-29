<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Repository;

use Gsu\SyllabusPortal\Entity\User;
use Gsu\SyllabusPortal\Entity\UserStatus;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class UserRepository
{
    /** @var string */
    protected const USER_KEY = '/users/identifiers/%s';

    /** @var string */
    protected const USER_STATUS_KEY = '/users/status/%s';


    /**
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(private CacheItemPoolInterface $cache)
    {
    }


    /**
     * @param string $userIdentifier
     * @return User
     */
    public function getUser(string $userIdentifier): User|null
    {
        if ($this->getStatus($userIdentifier)->isDisabled()) {
            return null;
        }

        $user = $this->getItem(self::USER_KEY, $userIdentifier)->get();
        return ($user instanceof User)
            ? $user
            : null;
    }


    /**
     * @param User $user
     * @return User
     */
    public function setUser(User $user): User
    {
        // Stop if user account is disabled
        $status = $this->getStatus($user->getUserIdentifier());
        if ($status->isDisabled()) {
            throw new \RuntimeException();
        }

        // Update login count. If count is exceeded, disable the account for increasing amount of time
        $status->incrementLogin((int) floor(time() / 60));
        if ($status->getLoginCount() >= 12) {
            if ($status->getDisabledCount() >= 12) {
                $status->setDisabledUntil(PHP_INT_MAX);
            } else {
                $status
                    ->setDisabledCount($status->getDisabledCount() + 1)
                    ->setDisabledUntil(time() + pow(2, $status->getDisabledCount() + 5));
            }
        }
        $this->setStatus($status);
        if ($status->isDisabled(time())) {
            throw new \RuntimeException();
        }

        // Stop if user already exists
        $item = $this->getItem(self::USER_KEY, $user->getUserIdentifier());
        if ($item->isHit()) {
            throw new \RuntimeException();
        }

        return $this->setItem(
            $item,
            $user,
            max($user->getExp() - $user->getNbf() - 30, 0)
        );
    }


    /**
     * @param string $username
     * @return UserStatus
     */
    public function getStatus(string $username): UserStatus
    {
        $status = $this->getItem(self::USER_STATUS_KEY, $username)->get();
        return ($status instanceof UserStatus)
            ? $status
            : new UserStatus($username);
    }


    /**
     * @param UserStatus $status
     * @return UserStatus
     */
    public function setStatus(UserStatus $status): UserStatus
    {
        return $this->setItem(
            $this->getItem(
                self::USER_STATUS_KEY,
                $status->getUsername()
            ),
            $status,
            $status->getDisabledUntil() < PHP_INT_MAX ? 2500000 : null
        );
    }


    /**
     * @param string $key
     * @param (string|int|float|null)[] ...$values
     * @return CacheItemInterface
     */
    protected function getItem(
        string $key,
        bool|float|int|string|null ...$values
    ): CacheItemInterface {
        $key = sprintf($key, ...$values);
        return $this->cache->getItem(hash('SHA256', $key));
    }


    /**
     * @template T
     * @param CacheItemInterface $item
     * @param T $value
     * @param int|\DateInterval|null $expiresAfter
     * @return T
     */
    protected function setItem(
        CacheItemInterface $item,
        mixed $value,
        int|\DateInterval|null $expiresAfter = null
    ): mixed {
        if ($expiresAfter !== null) {
            $item->expiresAfter($expiresAfter);
        }

        return $this->cache->save($item->set($value))
            ? $value
            : throw new \RuntimeException();
    }
}
