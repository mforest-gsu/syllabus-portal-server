<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Entity;

class UserStatus implements \JsonSerializable, \Stringable
{
    public function __construct(
        string $username = '',
        int $disabledUntil = 0,
        int $disabledCount = 0,
        int $loginInterval = 0,
        int $loginCount = 0
    ) {
        $this
            ->setUsername($username)
            ->setDisabledUntil($disabledUntil)
            ->setDisabledCount($disabledCount)
            ->setLoginInterval($loginInterval)
            ->setLoginCount($loginCount);
    }


    private string $username = '';
    public function getUsername(): string
    {
        return $this->username;
    }
    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }


    private int $disabledUntil = 0;
    public function getDisabledUntil(): int
    {
        return $this->disabledUntil;
    }
    public function setDisabledUntil(int $disabledUntil): static
    {
        $this->disabledUntil = $disabledUntil;
        return $this;
    }


    private int $disabledCount = 0;
    public function getDisabledCount(): int
    {
        return $this->disabledCount;
    }
    public function setDisabledCount(int $disabledCount): static
    {
        $this->disabledCount = $disabledCount;
        return $this;
    }


    public function isDisabled(int|null $rightNow = null): bool
    {
        return $this->getDisabledUntil() >= ($rightNow ?? time());
    }


    private int $loginInterval = 0;
    public function getLoginInterval(): int
    {
        return $this->loginInterval;
    }
    public function setLoginInterval(int $loginInterval): static
    {
        $this->loginInterval = $loginInterval;
        return $this;
    }


    private int $loginCount = 0;
    public function getLoginCount(): int
    {
        return $this->loginCount;
    }
    public function setLoginCount(int $loginCount): static
    {
        $this->loginCount = $loginCount;
        return $this;
    }


    public function incrementLogin(int $loginInterval): static
    {
        return ($loginInterval > $this->getLoginInterval())
            ? $this
                ->setLoginInterval($loginInterval)
                ->setLoginCount(1)
            : $this->setLoginCount($this->getLoginCount() + 1);
    }


    public function jsonSerialize(): mixed
    {
        return [
            'username' => $this->getUsername(),
            'disabled_until' => $this->getDisabledUntil(),
            'disabled_count' => $this->getDisabledCount(),
            'login_interval' => $this->getLoginInterval(),
            'login_count' => $this->getLoginCount()
        ];
    }


    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }


    public function hashCode(): string
    {
        return hash(
            'SHA256',
            json_encode([static::class, $this], JSON_THROW_ON_ERROR)
        );
    }
}
