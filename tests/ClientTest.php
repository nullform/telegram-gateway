<?php

namespace Nullform\TelegramGateway\Tests;

use Nullform\TelegramGateway\Client;
use Nullform\TelegramGateway\Parameters\SendVerificationMessageParameters;
use Nullform\TelegramGateway\Types\DeliveryStatus;
use Nullform\TelegramGateway\Types\RequestStatus;
use Nullform\TelegramGateway\Exceptions\HttpException;
use Nullform\TelegramGateway\Exceptions\TelegramGatewayApiException;
use Nullform\TelegramGateway\Types\VerificationStatus;

class ClientTest extends AbstractTestCase
{
    protected $test_code = '123456';
    protected $test_payload = 'test';

    /**
     * @throws TelegramGatewayApiException
     * @throws HttpException
     * @throws \Exception
     * @return RequestStatus
     */
    public function testCheckSendAbility()
    {
        $client = $this->getClient();

        sleep(6);

        $status = $client->checkSendAbility($this->getCredentials()['phone_number']);

        $this->assertInstanceOf(RequestStatus::class, $status);

        return $status;
    }

    /**
     * @param RequestStatus $status
     * @return RequestStatus
     * @throws TelegramGatewayApiException
     * @throws HttpException
     * @throws \Exception
     * @depends testCheckSendAbility
     */
    public function testSendVerificationMessage(RequestStatus $status)
    {
        $client = $this->getClient();
        $parameters = new SendVerificationMessageParameters();
        $parameters->code_length = \strlen($this->test_code);
        $parameters->request_id = $status->request_id;
        $parameters->ttl = 60;

        if ($this->getCredentials()['sender_username']) {
            $parameters->sender_username = $this->getCredentials()['sender_username'];
        }

        if ($this->getCredentials()['callback_url']) {
            $parameters->callback_url = $this->getCredentials()['callback_url'];
        }

        return $client->sendVerificationMessage(
            $this->getCredentials()['phone_number'],
            $parameters
        );
    }

    /**
     * @return RequestStatus
     * @throws TelegramGatewayApiException
     * @throws HttpException
     * @throws \Exception
     */
    public function testSendVerificationMessageWithCode()
    {
        $client = $this->getClient();
        $parameters = new SendVerificationMessageParameters();
        $parameters->code = $this->test_code;
        $parameters->payload = $this->test_payload;
        $parameters->ttl = 60;

        if ($this->getCredentials()['sender_username']) {
            $parameters->sender_username = $this->getCredentials()['sender_username'];
        }

        if ($this->getCredentials()['callback_url']) {
            $parameters->callback_url = $this->getCredentials()['callback_url'];
        }

        sleep(6);

        $status = $client->sendVerificationMessage(
            $this->getCredentials()['phone_number'],
            $parameters
        );

        $this->assertInstanceOf(RequestStatus::class, $status);
        $this->assertTrue($status->payload === $this->test_payload);

        return $status;
    }

    /**
     * @param RequestStatus $status
     * @return void
     * @throws \Exception
     * @depends testSendVerificationMessage
     */
    public function testCurlOptions($status)
    {
        $this->assertTrue(\is_string($status->request_id));

        $client = $this->getClient();

        $client->setCurlOption(\CURLOPT_TIMEOUT, 30);
        $client->setCurlOption(\CURLOPT_USERAGENT, Client::class);

        $this->assertEquals(30, $client->getCurlOptions()[\CURLOPT_TIMEOUT]);
        $this->assertEquals(Client::class, $client->getCurlOptions()[\CURLOPT_USERAGENT]);

        $client->setCurlOptions([]); // Cleanup.

        $this->assertArrayNotHasKey(\CURLOPT_TIMEOUT, $client->getCurlOptions());
        $this->assertArrayNotHasKey(\CURLOPT_USERAGENT, $client->getCurlOptions());

        $client->checkVerificationStatus($status->request_id);

        $info = $client->getCurlInfo();

        $this->assertArrayHasKey('url', $info);
        $this->assertTrue($info['url'] === 'https://gatewayapi.telegram.org/checkVerificationStatus');
    }

    /**
     * @return void
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @throws \Exception
     */
    public function testSendVerificationMessageWrongNumber()
    {
        $parameters = new SendVerificationMessageParameters();
        $parameters->code_length = \strlen($this->test_code);

        sleep(6);

        $this->expectException(TelegramGatewayApiException::class);
        $this->getClient()->sendVerificationMessage('+7111', $parameters);
    }

    /**
     * @param RequestStatus $status
     * @return void
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @throws \Exception
     * @depends testSendVerificationMessage
     */
    public function testRevokeVerificationMessage($status)
    {
        $client = $this->getClient();

        sleep(3);

        $result = $client->revokeVerificationMessage($status->request_id);

        $this->assertTrue($result);
    }

    /**
     * @param RequestStatus $status
     * @return void
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @throws \Exception
     * @depends testSendVerificationMessage
     */
    public function testCheckVerificationStatus($status)
    {
        $client = $this->getClient();

        $result = $client->checkVerificationStatus($status->request_id, $this->test_code);

        $this->assertInstanceOf(RequestStatus::class, $result);
        $this->assertInstanceOf(VerificationStatus::class, $result->verification_status);
        $this->assertTrue($result->verification_status->code_entered == $this->test_code);
        $this->assertInstanceOf(DeliveryStatus::class, $result->delivery_status);
    }

    /**
     * @param RequestStatus $status
     * @return void
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @throws \Exception
     * @depends testSendVerificationMessageWithCode
     */
    public function testCheckVerificationStatusWithCode($status)
    {
        $client = $this->getClient();

        $result = $client->checkVerificationStatus($status->request_id, $this->test_code);

        $this->assertInstanceOf(RequestStatus::class, $result);
        $this->assertInstanceOf(VerificationStatus::class, $result->verification_status);
        $this->assertInstanceOf(DeliveryStatus::class, $result->delivery_status);
    }
}
