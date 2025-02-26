<?php

namespace Nullform\TelegramGateway\Tests;

use Nullform\TelegramGateway\Exceptions\ReportBadPayloadException;
use Nullform\TelegramGateway\Exceptions\ReportDataCheckException;
use Nullform\TelegramGateway\ReportHandler;
use Nullform\TelegramGateway\Types\DeliveryStatus;
use Nullform\TelegramGateway\Types\RequestStatus;
use Nullform\TelegramGateway\Types\VerificationStatus;

class ReportHandlerTest extends AbstractTestCase
{
    protected $headers = [
        'X-Request-Signature' => 'f4778e1888c35d988b0b9f547d2caccbdf7c18740701bfe33ba6c83ffdbb8fa9',
        'X-Request-Timestamp' => '1733663990',
        'Accept-Charset'      => 'utf-8',
        'Accept'              => 'application/json',
        'Accept-Encoding'     => 'gzip, br',
        'Content-Length'      => 208,
        'Content-Type'        => 'application/json',
    ];

    protected $payload = '{"request_id":"923583492968596","phone_number":"1999123456726","request_cost":0,"delivery_status":{"status":"revoked","updated_at":1733663990},"verification_status":{"status":"expired","updated_at":1733663989}}';

    /**
     * @return void
     * @throws ReportDataCheckException
     * @throws \Exception
     */
    public function testReceive()
    {
        $handler = new ReportHandler($this->getCredentials()['token']);

        try {
            $handler->receive();
        } catch (\Exception $e) {
            $this->assertInstanceOf(ReportDataCheckException::class, $e);
        }

        $this->expectException(ReportDataCheckException::class);

        $handler->getRequestTimestamp();
    }

    /**
     * @return void
     * @throws ReportDataCheckException
     * @throws \Exception
     */
    public function testCheckRequest()
    {
        $handler = new ReportHandler($this->getCredentials()['token']);

        $timestamp = $handler->getRequestTimestamp($this->headers);

        $valid = $handler->checkRequest($this->headers, $this->payload);

        $this->assertFalse($valid);
        $this->assertEquals(1733663990, $timestamp);
    }

    /**
     * @return void
     * @throws ReportBadPayloadException
     * @throws \Exception
     */
    public function testParseReport()
    {
        $handler = new ReportHandler($this->getCredentials()['token']);

        $status = $handler->parseReport($this->payload);

        $status = new RequestStatus((string)$status); // Check for correct converting to string.

        $this->assertInstanceOf(RequestStatus::class, $status);
        $this->assertTrue($status->request_id === '923583492968596');
        $this->assertTrue($status->phone_number === '1999123456726');
        $this->assertTrue($status->request_cost === 0);
        $this->assertTrue($status->is_refunded === null);
        $this->assertTrue($status->remaining_balance === null);
        $this->assertTrue($status->delivery_status->status === DeliveryStatus::STATUS_REVOKED);
        $this->assertTrue($status->delivery_status->updated_at === 1733663990);
        $this->assertTrue($status->verification_status->status === VerificationStatus::STATUS_EXPIRED);
        $this->assertTrue($status->verification_status->updated_at === 1733663989);
        $this->assertTrue($status->verification_status->code_entered === null);
    }
}
