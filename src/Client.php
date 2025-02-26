<?php

namespace Nullform\TelegramGateway;

use Nullform\TelegramGateway\Exceptions\HttpException;
use Nullform\TelegramGateway\Exceptions\TelegramGatewayApiException;
use Nullform\TelegramGateway\Parameters\SendVerificationMessageParameters;
use Nullform\TelegramGateway\Types\RequestStatus;

/**
 * Client for Telegram Gateway API.
 *
 * @link https://core.telegram.org/gateway/api
 */
class Client
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://gatewayapi.telegram.org/';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $curlOptions = [];

    /**
     * @var array
     */
    protected $curlInfo = [];

    /**
     * @param string $token API token.
     * @see https://core.telegram.org/gateway/verification-tutorial#obtaining-your-api-token
     */
    public function __construct($token)
    {
        $this->token = (string)$token;
    }

    /**
     * Get current options for a cURL transfer.
     *
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->curlOptions;
    }

    /**
     * Set multiple (rewrite) options for a cURL transfer.
     *
     * @param array $curlOptions An array specifying which options to set and their values.
     * @return $this
     * @see https://www.php.net/manual/en/function.curl-setopt-array.php
     */
    public function setCurlOptions($curlOptions)
    {
        $this->curlOptions = (array)$curlOptions;

        return $this;
    }

    /**
     * Set an option for a cURL transfer.
     *
     * @param int $option
     * @param mixed $value
     * @return $this
     * @see https://www.php.net/manual/en/function.curl-setopt.php
     */
    public function setCurlOption($option, $value)
    {
        $this->curlOptions[(int)$option] = $value;

        return $this;
    }

    /**
     * Information about the last request via cURL.
     *
     * @return array
     * @see \curl_getinfo()
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }

    /**
     * Use this method to send a verification message.
     * Charges will apply according to the pricing plan for each successful message delivery.
     *
     * Note that this method is always free of charge when used to send codes to your own phone number.
     *
     * @param string $phoneNumber The phone number to which you want to send a verification message, in the E.164 format.
     * @param SendVerificationMessageParameters $parameters
     * @return RequestStatus On success, returns a RequestStatus object.
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @link https://core.telegram.org/gateway/api#sendverificationmessage
     * @see https://en.wikipedia.org/wiki/E.164
     */
    public function sendVerificationMessage($phoneNumber, $parameters)
    {
        $data = \array_merge(
            ['phone_number' => (string)$phoneNumber],
            !empty($parameters) ? (array)$parameters : []
        );

        return new RequestStatus($this->callApi('sendVerificationMessage', $data));
    }

    /**
     * Use this method to optionally check the ability to send a verification message to the specified phone number.
     * If the ability to send is confirmed, a fee will apply according to the pricing plan.
     *
     * After checking, you can send a verification message using the sendVerificationMessage method,
     * providing the request_id from this response.
     *
     * Within the scope of a request_id, only one fee can be charged.
     * Calling sendVerificationMessage once with the returned request_id will be free of charge,
     * while repeated calls will result in an error. Conversely, calls that don't include a request_id will spawn
     * new requests and incur the respective fees accordingly.
     *
     * Note that this method is always free of charge when used to send codes to your own phone number.
     *
     * In case the message can be sent, returns a RequestStatus object. Otherwise, an appropriate error will be returned.
     *
     * @param string $phoneNumber
     * @return RequestStatus In case the message can be sent, returns a RequestStatus object.
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @link https://core.telegram.org/gateway/api#checksendability
     */
    public function checkSendAbility($phoneNumber)
    {
        $data = ['phone_number' => $phoneNumber];

        return new RequestStatus($this->callApi('checkSendAbility', $data));
    }

    /**
     * Use this method to check the status of a verification message that was sent previously.
     *
     * If the code was generated by Telegram for you, you can also verify the correctness of the code
     * entered by the user using this method.
     *
     * Even if you set the code yourself, it is recommended to call this method after the user has successfully
     * entered the code, passing the correct code in the code parameter, so that we can track the conversion rate
     * of your verifications.
     *
     * @param string $requestId The unique identifier of the verification request whose status you want to check.
     * @param string|null $code The code entered by the user.
     *                          If provided, the method checks if the code is valid for the relevant request.
     * @return RequestStatus On success, returns a RequestStatus object.
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @link https://core.telegram.org/gateway/api#checkverificationstatus
     */
    public function checkVerificationStatus($requestId, $code = null)
    {
        $data = ['request_id' => $requestId];

        if ($code && \is_numeric($code)) {
            $data['code'] = $code;
        }

        return new RequestStatus($this->callApi('checkVerificationStatus', $data));
    }

    /**
     * Use this method to revoke a verification message that was sent previously.
     *
     * Returns True if the revocation request was received.
     *
     * However, this does not guarantee that the message will be deleted. For example, if the message
     * has already been delivered or read, it will not be removed.
     *
     * @param string $requestId The unique identifier of the request whose verification message you want to revoke.
     * @return bool
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @link https://core.telegram.org/gateway/api#revokeverificationmessage
     */
    public function revokeVerificationMessage($requestId)
    {
        $data = ['request_id' => $requestId];

        $result = \json_decode($this->callApi('revokeVerificationMessage', $data));

        return !empty($result);
    }

    /**
     * Call Telegram Gateway API.
     *
     * @param string $method
     * @param array|object $data Data that will be encoded in JSON and sent along with the request.
     * @return string Result (JSON string).
     * @throws HttpException
     * @throws TelegramGatewayApiException
     * @see https://core.telegram.org/gateway/api#making-requests
     */
    protected function callApi($method, $data)
    {
        $method = (string)$method;
        $body = \json_encode($data);
        $url = $this->baseUrl . $method;

        $curl = \curl_init($url);
        $curlOptions = $this->curlOptions;

        $curlOptions[\CURLOPT_POST] = true;
        $curlOptions[\CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[\CURLOPT_POSTFIELDS] = $body;
        $curlOptions[\CURLOPT_FOLLOWLOCATION] = true;

        if (!isset($curlOptions[\CURLOPT_HTTPHEADER])) {
            $curlOptions[\CURLOPT_HTTPHEADER] = [];
        }

        $curlOptions[\CURLOPT_HTTPHEADER] = \array_merge($curlOptions[\CURLOPT_HTTPHEADER], [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
            'Content-Length: ' . \strlen($body),
        ]);

        \curl_setopt_array($curl, $curlOptions);

        $response = \curl_exec($curl);
        $parsedResponse = \json_decode((string)$response, true);
        $curlError = \curl_error($curl);

        $this->curlInfo = \curl_getinfo($curl);

        \curl_close($curl);

        if (empty($parsedResponse['ok'])) { // Fail

            if (!empty($this->curlInfo['http_code'])) {

                throw new TelegramGatewayApiException(
                    !empty($parsedResponse['error']) ? (string)$parsedResponse['error'] : 'Unknown Telegram Gateway error',
                    (int)$this->curlInfo['http_code']
                );

            } else {

                throw new HttpException($curlError ?: 'Unknown cURL error');

            }

        }

        return $parsedResponse['result'] ?: '';
    }
}
