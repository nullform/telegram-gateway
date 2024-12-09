<?php

// Create credentials.php with similar content.

return [

    // Required. Before invoking API methods, you must obtain an access token in the Telegram Gateway account settings.
    // https://core.telegram.org/gateway/api#authorization
    'token'           => '',

    // Required. The phone number to which you want to send a verification message, in the E.164 format.
    'phone_number'    => '',

    // Optional. An HTTPS URL where you want to receive delivery reports related to the sent message, 0-256 bytes.
    // https://core.telegram.org/gateway/api#report-delivery
    'callback_url'    => '',

    // Optional. Username of the Telegram channel from which the code will be sent.
    // The specified channel, if any, must be verified and owned by the same account who owns the Gateway API token.
    // https://core.telegram.org/gateway/api#sendverificationmessage
    'sender_username' => '',

];
