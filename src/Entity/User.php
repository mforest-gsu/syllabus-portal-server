<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, \JsonSerializable, \Stringable
{
    /**
     * @param string[] $roles
     * @param mixed[] $attributes
     */
    public function __construct(
        string $userIdentifier = '',
        array $roles = ['ROLE_USER'],
        string $aud = '',
        string $iss = '',
        int $iat = 0,
        int $nbf = 0,
        int $exp = 0,
        string $name = '',
        string $nonce = '',
        string $preferredUsername = '',
        string $sub = '',
        string $tid = '',
        array $attributes = [],
    ) {
        $this
            ->setUserIdentifier($userIdentifier)
            ->setRoles($roles)
            ->setAud($aud)
            ->setIss($iss)
            ->setIat($iat)
            ->setNbf($nbf)
            ->setExp($exp)
            ->setName($name)
            ->setNonce($nonce)
            ->setPreferredUsername($preferredUsername)
            ->setSub($sub)
            ->setTid($tid)
            ->setAttributes($attributes);
    }


    private string $userIdentifier = '';
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
    public function setUserIdentifier(string $userIdentifier): static
    {
        $this->userIdentifier = $userIdentifier;
        return $this;
    }


    /**
     * @var string[] $roles
     */
    private array $roles = ['ROLE_USER'];
    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }


    private string $aud = '';
    public function getAud(): string
    {
        return $this->aud;
    }
    public function setAud(string $aud): static
    {
        $this->aud = $aud;
        return $this;
    }


    private string $iss = '';
    public function getIss(): string
    {
        return $this->iss;
    }
    public function setIss(string $iss): static
    {
        $this->iss = $iss;
        return $this;
    }


    private int $iat = 0;
    public function getIat(): int
    {
        return $this->iat;
    }
    public function setIat(int $iat): static
    {
        $this->iat = $iat;
        return $this;
    }


    private int $nbf = 0;
    public function getNbf(): int
    {
        return $this->nbf;
    }
    public function setNbf(int $nbf): static
    {
        $this->nbf = $nbf;
        return $this;
    }


    private int $exp = 0;
    public function getExp(): int
    {
        return $this->exp;
    }
    public function setExp(int $exp): static
    {
        $this->exp = $exp;
        return $this;
    }


    private string $name = '';
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }


    private string $nonce = '';
    public function getNonce(): string
    {
        return $this->nonce;
    }
    public function setNonce(string $nonce): static
    {
        $this->nonce = $nonce;
        return $this;
    }


    private string $preferredUsername = '';
    public function getPreferredUsername(): string
    {
        return $this->preferredUsername;
    }
    public function setPreferredUsername(string $preferredUsername): static
    {
        $this->preferredUsername = $preferredUsername;
        return $this;
    }


    private string $sub = '';
    public function getSub(): string
    {
        return $this->sub;
    }
    public function setSub(string $sub): static
    {
        $this->sub = $sub;
        return $this;
    }


    private string $tid = '';
    public function getTid(): string
    {
        return $this->tid;
    }
    public function setTid(string $tid): static
    {
        $this->tid = $tid;
        return $this;
    }


    /**
     * @var mixed[] $attributes
     */
    private array $attributes = [];
    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    /**
     * @param mixed[] $attributes
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }


    public function eraseCredentials(): void
    {
    }


    public function jsonSerialize(): mixed
    {
        return $this->getAttributes();
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
