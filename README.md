# Telegram Gateway PHP Package

The PHP package for sending verification codes via [Telegram Gateway](https://gateway.telegram.org/).

> The Gateway API is an HTTP-based interface created for developers looking to deliver automated messages, such as verification codes, to users who registered their phone number on Telegram.

## Requirements

- PHP >= 5.6

## Installation

```shell
composer require nullform/telegram-gateway
```

First, you need to get an access token from your Telegram Gateway account settings: https://gateway.telegram.org.

## Usage examples

Simplest example:

```php
$client = new \Nullform\TelegramGateway\Client('API Token');
$parameters = new \Nullform\TelegramGateway\Parameters\SendVerificationMessageParameters();
$parameters->code = '123456';

try {
    $requestStatus = $client->sendVerificationMessage('+19999999999', $parameters);
    // ...
} catch (\Exception $exception) {
    // ...
}
```

Extended example:

```php
use Nullform\TelegramGateway\Client;
use Nullform\TelegramGateway\Parameters\SendVerificationMessageParameters;
use Nullform\TelegramGateway\AbstractException;

$token = 'API Token';
$number = '+19999999999'; // The phone number to which you want to send a verification message.

$client = new Client($token);
$client->setCurlOption(CURLOPT_TIMEOUT, 10); // You can set your own cURL option...
$client->setCurlOptions([CURLOPT_TIMEOUT => 10]); // ... or multiple options.

$parameters = new SendVerificationMessageParameters();
$parameters->code_length = 6; // Set your own code or code length.
$parameters->payload = 'custom data'; // Custom payload.
$parameters->ttl = 60; // Time-to-live (1 minute) before the message expires.
$parameters->callback_url = 'https://example.com/webhook/telegram-gateway'; // Your callback URL.
$parameters->sender_username = 'my_tg_channel'; // Channel from which the code will be sent.

try {

    // Instance of RequestStatus.
    $requestStatus = $client->sendVerificationMessage($number, $parameters);

    // Last request info.
    $curlInfo = $client->getCurlInfo();

} catch (AbstractException $exception) { // Unable to send or error during sending.

    // ...

}
```

You can check send ability first:

```php
use Nullform\TelegramGateway\Client;
use Nullform\TelegramGateway\Parameters\SendVerificationMessageParameters;
use Nullform\TelegramGateway\AbstractException;

$token = 'API Token';
$number = '+19999999999';

$client = new Client($token);

try {

    // Throws an exception if there is no ability.
    $abilityStatus = $client->checkSendAbility();

    try {

        $parameters = new SendVerificationMessageParameters();

        // The unique identifier of a previous request from checkSendAbility.
        // If provided, this request will be free of charge.
        $parameters->request_id = $abilityStatus->request_id;

        $parameters->code = '123456'; // Your custom code. A fully numeric string of 4-8 chars.

        $requestStatus = $client->sendVerificationMessage($number, $parameters);

        // ...

    } catch (AbstractException $exception) { // Can't send.

        // ...

    }

} catch (AbstractException $exception) { // No ability.

    // ...

}

```

Use checkVerificationStatus method to check the status of a verification message that was sent previously. If the code was generated by Telegram for you, you can also verify the correctness of the code entered by the user using this method.

```php
use Nullform\TelegramGateway\Client;
use Nullform\TelegramGateway\AbstractException;
use Nullform\TelegramGateway\Types\VerificationStatus;

$token = 'API Token';
$client = new Client($token);
$requestId = 'request id';
$code = $_POST['code'];

try {

    $status = $client->checkVerificationStatus($requestId, $code);

    if ($status->verification_status->status == VerificationStatus::STATUS_CODE_VALID) {
        // Success.
    }

} catch (AbstractException $exception) {

    // ...

}
```

## Report delivery

You can receive delivery reports from the Telegram Gateway API. Just pass your callback URL to the sendVerificationMessage method and handle the POST request:

```php
use Nullform\TelegramGateway\ReportHandler;
use Nullform\TelegramGateway\AbstractException;

$token = 'API Token';
$handler = new ReportHandler($token);

try {

    // Instance of RequestStatus.
    $requestStatus = $handler->receive();

} catch (AbstractException $exception) {

    // ...

}
```

## Methods

### Client

- Client::**__construct**(*string* $token)
- Client::**getCurlOptions**(): *array*
- Client::**setCurlOptions**(*array* $curlOptions): *Client*
- Client::**setCurlOption**(*int* $option, *mixed* \$value): *Client*
- Client::**getCurlInfo**(): *array*
- Client::**sendVerificationMessage**(*string* $phoneNumber, *SendVerificationMessageParameters* \$parameters): *RequestStatus*
- Client::**checkSendAbility**(*string* $phoneNumber): *RequestStatus*
- Client::**checkVerificationStatus**(*string* $requestId, *?string* \$code = *null*): *RequestStatus*
- Client::**revokeVerificationMessage**(*string* $requestId): *bool*

### ReportHandler

- ReportHandler::**__construct**(*string* $token)
- ReportHandler::**receive**(): *RequestStatus*
- ReportHandler::**parseReport**(*string* $payload): *RequestStatus*
- ReportHandler::**checkRequest**(*array* $httpRequestHeaders, *string* \$httpRequestBody): *bool*
- ReportHandler::**getRequestTimestamp**(*?array* $customHttpRequestHeaders = *null*): *int*

## Tests

For unit tests, you need to create a *credentials.php* file (see *tests/credentials.example.php*).

```shell
php composer.phar test tests
```
