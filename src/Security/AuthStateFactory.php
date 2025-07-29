<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Security;

use Gsu\SyllabusPortal\Entity\User;

class AuthStateFactory
{
    /**
     * @param string $webUri
     * @param string $authUri
     * @param string $clientId
     * @param string $appSecret
     * @param int $expiresAfter
     */
    public function __construct(
        private string $webUri,
        private string $authUri,
        private string $clientId,
        private string $appSecret,
        private int $expiresAfter = 60
    ) {
    }


    /**
     * @param User $user
     * @return string
     */
    public function createWebUri(User $user): string
    {
        return sprintf(
            '%s/auth?%s',
            $this->webUri,
            http_build_query([
                "accessToken" => $user->getUserIdentifier(),
                "expiresIn" => $user->getExp() - $user->getNbf() - 30
            ], "", null, PHP_QUERY_RFC3986)
        );
    }


    /**
     * @param string $redirectUri
     * @param string|null $nonce
     * @param string|null $state
     * @return string
     */
    public function createLoginUri(
        string $redirectUri,
        string|null $nonce = null,
        string|null $state = null
    ): string {
        $nonce ??= $this->createNonce();
        $state ??= $this->createState($nonce);

        return sprintf(
            "%s?%s",
            $this->authUri,
            http_build_query([
                "client_id" => $this->clientId,
                "response_type" => "id_token",
                "redirect_uri" => $redirectUri,
                "scope" => "openid profile",
                "nonce" => $nonce,
                "response_mode" => "form_post",
                "state" => $state,
                "prompt" => "select_account"
            ], "", null, PHP_QUERY_RFC3986)
        );
    }


    /**
     * @param int<1,max> $bytes
     * @return string
     */
    public function createNonce(int $bytes = 32): string
    {
        return base64_encode(random_bytes($bytes));
    }


    /**
     * @param string $nonce
     * @param int|null $expiresAfter
     * @return string
     */
    public function createState(
        string $nonce,
        int|null $expiresAfter = null
    ): string {
        $exp = strval(time() + ($expiresAfter ?? $this->expiresAfter));
        return implode(".", [
            base64_encode($nonce),
            base64_encode($exp),
            $this->signState($nonce, $exp)
        ]);
    }


    /**
     * @param string $state
     * @return string
     */
    public function validateState(string $state): string
    {
        [$nonce, $exp, $sig] = [...explode(".", $state, 3), '', '', ''];

        $nonce = base64_decode($nonce, true);
        if (!is_string($nonce)) {
            throw new \RuntimeException();
        }

        $exp = base64_decode($exp, true);
        if (!is_string($exp) || !is_numeric($exp) || intval($exp) <= time()) {
            throw new \RuntimeException();
        }

        if ($sig !== $this->signState($nonce, $exp)) {
            throw new \RuntimeException();
        }

        return $nonce;
    }


    /**
     * @param string $nonce
     * @param int|string $exp
     * @return string
     */
    protected function signState(
        string $nonce,
        int|string $exp
    ): string {
        return base64_encode(hash_hmac(
            'SHA256',
            $nonce . $exp,
            $this->appSecret,
            true
        ));
    }
}
