<?php

namespace Jyokyoku\AjaxMailer;

class CsrfToken
{
    const HASH_ALGO = 'sha256';

    /**
     * @return string
     * @throws \BadMethodCallException
     */
    public static function generate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            throw new \BadMethodCallException('Session is not active.');
        }

        return hash(self::HASH_ALGO, session_id());
    }

    /**
     * @param $token
     * @param bool $checkLifetime
     * @param int $seconds
     * @return bool
     */
    public static function validate($token)
    {
        return self::generate() === $token;
    }
}