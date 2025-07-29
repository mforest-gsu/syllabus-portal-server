<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Entity;

class UserAuthCode implements \JsonSerializable, \Stringable
{
    public function __construct(
        string $idToken = '',
        string $state = ''
    ) {
        $this
            ->setIdToken($idToken)
            ->setState($state);
    }


    private string $idToken = '';
    public function getIdToken(): string
    {
        return $this->idToken;
    }
    public function setIdToken(string $idToken): static
    {
        $this->idToken = $idToken;
        return $this;
    }


    private string $state = '';
    public function getState(): string
    {
        return $this->state;
    }
    public function setState(string $state): static
    {
        $this->state = $state;
        return $this;
    }


    public function jsonSerialize(): mixed
    {
        return [
            'id_token' => $this->getIdToken(),
            'state' => $this->getState()
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
