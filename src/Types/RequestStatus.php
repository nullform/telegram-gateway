<?php

namespace Nullform\TelegramGateway\Types;

use Nullform\TelegramGateway\AbstractType;

/**
 * This object represents the status of a verification message request.
 *
 * @link https://core.telegram.org/gateway/api#requeststatus
 */
class RequestStatus extends AbstractType
{
    /**
     * Unique identifier of the verification request.
     *
     * @var string
     */
    public $request_id;

    /**
     * The phone number to which the verification code was sent, in the E.164 format.
     *
     * @var string
     * @see https://en.wikipedia.org/wiki/E.164
     */
    public $phone_number;

    /**
     * Total request cost incurred by either checkSendAbility or sendVerificationMessage.
     *
     * @var float|int
     */
    public $request_cost;

    /**
     * Optional. If True, the request fee was refunded.
     *
     * @var null|bool
     */
    public $is_refunded = null;

    /**
     * Optional. Remaining balance in credits. Returned only in response to a request that incurs a charge.
     *
     * @var null|float|int
     */
    public $remaining_balance = null;

    /**
     * Optional. The current message delivery status. Returned only if a verification message was sent to the user.
     *
     * @var null|DeliveryStatus
     */
    public $delivery_status = null;

    /**
     * Optional. The current status of the verification process.
     *
     * @var null|VerificationStatus
     */
    public $verification_status = null;

    /**
     * Optional. Custom payload if it was provided in the request, 0-256 bytes.
     *
     * @var null|string
     */
    public $payload = null;

    /**
     * @inheritDoc
     */
    protected function map($data)
    {
        parent::map($data);

        if (!empty($this->delivery_status)) {
            $this->delivery_status = new DeliveryStatus($this->delivery_status);
        }
        if (!empty($this->verification_status)) {
            $this->verification_status = new VerificationStatus($this->verification_status);
        }
    }
}
