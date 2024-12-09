<?php

namespace Nullform\TelegramGateway\Tests;

use Nullform\TelegramGateway\Client;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    /**
     * @return Client
     * @throws \Exception
     */
    protected function getClient()
    {
        return new Client($this->getCredentials()['token']);
    }

    /**
     * @return array{token: string, phone_number: string, callback_url: string, sender_username: string}
     * @throws \Exception
     */
    protected function getCredentials()
    {
        $required = ['token', 'phone_number'];

        if (\is_file(__DIR__ . DIRECTORY_SEPARATOR . 'credentials.php')) {
            $credentials = include __DIR__ . DIRECTORY_SEPARATOR . 'credentials.php';
        } else {
            throw new \Exception("Can't load credentials file");
        }

        foreach ($credentials as $key => $value) {
            if (empty($value) && \in_array($key, $required)) {
                throw new \Exception("Empty value in credentials: " . \htmlspecialchars($key));
            }
        }

        return $credentials;
    }
}
