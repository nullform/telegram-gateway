<?php

namespace Nullform\TelegramGateway\Parameters;

use Nullform\TelegramGateway\AbstractParameters;
use Nullform\TelegramGateway\Client;

/**
 * @link https://core.telegram.org/gateway/api#sendverificationmessage
 */
class SendVerificationMessageParameters extends AbstractParameters
{
    /**
     * The unique identifier of a previous request from checkSendAbility.
     *
     * If provided, this request will be free of charge.
     *
     * @var null|string
     * @see Client::checkSendAbility()
     */
    public $request_id = null;

    /**
     * Username of the Telegram channel from which the code will be sent.
     * The specified channel, if any, must be verified and owned by the same account who owns the Gateway API token.
     *
     * @var null|string
     */
    public $sender_username = null;

    /**
     * The verification code.
     *
     * Use this parameter if you want to set the verification code yourself.
     * Only fully numeric strings between 4 and 8 characters in length are supported.
     * If this parameter is set, code_length is ignored.
     *
     * @var null|string
     */
    public $code = null;

    /**
     * The length of the verification code if Telegram needs to generate it for you. Supported values are from 4 to 8.
     *
     * This is only relevant if you are not using the code parameter to set your own code.
     * Use the checkVerificationStatus method with the code parameter to verify the code entered by the user.
     *
     * @var null|int
     */
    public $code_length = null;

    /**
     * An HTTPS URL where you want to receive delivery reports related to the sent message, 0-256 bytes.
     *
     * @var null|string
     */
    public $callback_url = null;

    /**
     * Custom payload, 0-128 bytes.
     *
     * This will not be displayed to the user, use it for your internal processes.
     *
     * @var null|string
     */
    public $payload = null;

    /**
     * Time-to-live (in seconds) before the message expires.
     *
     * If the message is not delivered or read within this time, the request fee will be refunded.
     *
     * Supported values are from 30 to 3600.
     *
     * @var null|int
     */
    public $ttl = null;
}
