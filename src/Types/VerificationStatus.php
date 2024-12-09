<?php

namespace Nullform\TelegramGateway\Types;

use Nullform\TelegramGateway\AbstractType;

/**
 * This object represents the verification status of a code.
 *
 * @link https://core.telegram.org/gateway/api#verificationstatus
 */
class VerificationStatus extends AbstractType
{
    const STATUS_CODE_VALID = 'code_valid';
    const STATUS_CODE_INVALID = 'code_invalid';
    const STATUS_CODE_MAX_ATTEMPTS_EXCEEDED = 'code_max_attempts_exceeded';
    const STATUS_EXPIRED = 'expired';

    /**
     * The current status of the verification process. One of the following:
     * - code_valid – the code entered by the user is correct,
     * - code_invalid – the code entered by the user is incorrect,
     * - code_max_attempts_exceeded – the maximum number of attempts to enter the code has been exceeded,
     * - expired – the code has expired and can no longer be used for verification.
     *
     * @var string
     * @see VerificationStatus::STATUS_CODE_VALID
     * @see VerificationStatus::STATUS_CODE_INVALID
     * @see VerificationStatus::STATUS_CODE_MAX_ATTEMPTS_EXCEEDED
     * @see VerificationStatus::STATUS_EXPIRED
     */
    public $status;

    /**
     * The timestamp for this particular status. Represents the time when the status was last updated.
     *
     * @var int
     */
    public $updated_at;

    /**
     * Optional. The code entered by the user.
     *
     * @var null|string
     */
    public $code_entered = null;
}
