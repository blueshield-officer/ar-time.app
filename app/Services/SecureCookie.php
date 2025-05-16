<?php

namespace App\Services;

class SecureCookie {
    private $defaultExpires;
    public function __construct()
    {
        $this->defaultExpires = time() + (3 * 30 * 24 * 60 * 60);
    }

    private array $defaultOptions = [
        'expires' => 0,
        'path' => '/',
        // todo change
//        'domain' => '.ar-time.org',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ];

    public function setCookie(
        string $name,
        string $value,
        ?int $expires = null,
        array $additionalOptions = []
    ): bool {
        $options = array_merge(
            $this->defaultOptions,
            [
                'expires' => $expires ?? $this->defaultExpires,
            ],
            $additionalOptions
        );

        return setcookie($name, $value, $options);
    }

    public function deleteCookie(string $name): bool {
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'none'
        ]);
    }
}