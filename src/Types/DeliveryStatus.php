<?php

namespace Nullform\TelegramGateway\Types;

use Nullform\TelegramGateway\AbstractType;

/**
 * This object represents the delivery status of a message.
 *
 * @link https://core.telegram.org/gateway/api#deliverystatus
 */
class DeliveryStatus extends AbstractType
{
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    /**
     * The current status of the message. One of the following:
     * - sent – the message has been sent to the recipient's device(s),
     * - delivered – the message has been delivered to the recipient's device(s),
     * - read – the message has been read by the recipient,
     * - expired – the message has expired without being delivered or read,
     * - revoked – the message has been revoked.
     *
     * @var string
     * @see DeliveryStatus::STATUS_SENT
     * @see DeliveryStatus::STATUS_DELIVERED
     * @see DeliveryStatus::STATUS_READ
     * @see DeliveryStatus::STATUS_EXPIRED
     * @see DeliveryStatus::STATUS_REVOKED
     */
    public $status;

    /**
     * The timestamp when the status was last updated.
     *
     * @var int
     */
    public $updated_at;
}
