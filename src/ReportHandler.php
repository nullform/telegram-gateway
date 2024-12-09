<?php

namespace Nullform\TelegramGateway;

use Nullform\TelegramGateway\Exceptions\ReportBadPayloadException;
use Nullform\TelegramGateway\Exceptions\ReportDataCheckException;
use Nullform\TelegramGateway\Types\RequestStatus;

/**
 * Telegram Gateway API report handler.
 *
 * @link https://core.telegram.org/gateway/api#report-delivery
 */
class ReportHandler
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @param string $token API token.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Receiving a report from the Telegram Gateway API (with origin confirmation and integrity check).
     *
     * ```
     * $handler = new ReportHandler($token);
     * try {
     *     $requestStatus = $handler->receive();
     * } catch (\Exception $e) {
     *     echo $e->getMessage();
     * }
     * ```
     *
     * @return RequestStatus
     * @throws ReportBadPayloadException
     * @throws ReportDataCheckException
     * @uses ReportHandler::checkRequest()
     * @uses ReportHandler::parseReport()
     * @see https://core.telegram.org/gateway/api#report-delivery
     */
    public function receive()
    {
        $body = (string)\file_get_contents('php://input');

        $valid = $this->checkRequest($this->getAllHeaders(), $body);

        if (!$valid) {
            throw new ReportDataCheckException('Invalid request');
        }

        return $this->parseReport($body);
    }

    /**
     * @param string $payload
     * @return RequestStatus
     * @throws ReportBadPayloadException
     */
    public function parseReport($payload)
    {
        if (empty($payload)) {
            throw new ReportBadPayloadException('Empty payload');
        }

        if (!\is_string($payload) || !\json_decode($payload, true)) {
            throw new ReportBadPayloadException('Invalid payload');
        }

        return new RequestStatus($payload);

    }

    /**
     * Confirmation of origin and verification of report integrity.
     *
     * @param array $httpRequestHeaders
     * @param string $httpRequestBody
     * @return bool
     * @throws ReportDataCheckException
     */
    public function checkRequest($httpRequestHeaders, $httpRequestBody)
    {
        $timestamp = $this->getRequestTimestampFromHeaders($httpRequestHeaders);
        $signature = $this->getRequestSignatureFromHeaders($httpRequestHeaders);
        $dataCheckString = $timestamp . "\n" . $httpRequestBody;
        $secretKey = \hash('sha256', $this->token);

        return \hash_hmac('sha256', $dataCheckString, \hex2bin($secretKey)) == $signature;
    }

    /**
     * Unix timestamp of when the relevant report was submitted by the server.
     *
     * @param null|array $customHttpRequestHeaders Optional. For custom HTTP headers.
     * @return int
     * @throws ReportDataCheckException
     */
    public function getRequestTimestamp($customHttpRequestHeaders = null)
    {
        return $this->getRequestTimestampFromHeaders(
            \is_null($customHttpRequestHeaders)
                ? $this->getAllHeaders()
                : (array)$customHttpRequestHeaders
        );
    }

    /**
     * @param array $httpRequestHeaders
     * @return int
     * @throws ReportDataCheckException
     */
    protected function getRequestTimestampFromHeaders($httpRequestHeaders)
    {
        if (empty($httpRequestHeaders['X-Request-Timestamp'])) {
            throw new ReportDataCheckException('Empty request timestamp');
        }

        return (int)$httpRequestHeaders['X-Request-Timestamp'];
    }

    /**
     * @param array $httpRequestHeaders
     * @return string
     * @throws ReportDataCheckException
     */
    protected function getRequestSignatureFromHeaders($httpRequestHeaders)
    {
        if (empty($httpRequestHeaders['X-Request-Signature'])) {
            throw new ReportDataCheckException('Empty request signature');
        }

        return (string)$httpRequestHeaders['X-Request-Signature'];
    }

    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * A polyfill for getallheaders().
     *
     * @return array The HTTP header key/value pairs.
     * @link https://packagist.org/packages/ralouphie/getallheaders
     * @see \getallheaders()
     */
    protected function getAllHeaders()
    {
        $headers = [];

        $copy_server = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];

        foreach ($_SERVER as $key => $value) {
            if (\substr($key, 0, 5) === 'HTTP_') {
                $key = \substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = \str_replace(' ', '-', \ucwords(\strtolower(\str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . \base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}
