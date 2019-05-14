<?php

use CLImax\ApplicationUtf8;
use CLImax\Plugins\HighlightPlugin;
use jalsoedesign\PersistentOAuthGoogleClient\Client;
use jalsoedesign\PersistentOAuthGoogleClient\ClientConfiguration;

require_once(__DIR__ . '/../../vendor/autoload.php');

$config = new ClientConfiguration();
$config->setAuthConfigPath(__DIR__ . '/../../lib/oauth.json');
$config->setScopes([Google_Service_Gmail::GMAIL_READONLY]);
$config->setTokenPath(__DIR__ . '/../../lib/gmail-user-token.json');

$client = new Client($config);

$client->setAuthCodeFromUrlCallback(function($authUrl) {
    printf('The auth URL is: %s', $authUrl);
    echo PHP_EOL;

    printf('Please open the above URL, log in, and insert the code below:');
    echo PHP_EOL;

    printf('Code > ');
    $code = fgets(STDIN);
    return rtrim($code);
});

$googleClient = $client->getGoogleClient();
$service = new Google_Service_Gmail($googleClient);

scanEmails($service);

function scanEmails(Google_Service_Gmail $service, $userId = 'me') {
    $pageToken = null;
    $options = [];

    do {
        if ($pageToken) {
            $options['pageToken'] = $pageToken;
        }

        printf(sprintf('Scanning messages for %s..', $userId));
        echo PHP_EOL;

        $messagesResponse = $service->users_messages->listUsersMessages($userId, $options);

        /** @var Google_Service_Gmail_Message[] $messages */
        $messages = $messagesResponse->getMessages();

        if (empty($messages)) {
            break;
        }

        $messageIds = [];

        foreach ($messages as $message) {
            $messageIds[] = $message->getId();
        }

        foreach ($messageIds as $messageId) {
            scanMessage($service, $userId, $messageId);
        }

        $pageToken = $messagesResponse->getNextPageToken();
    } while ($pageToken);
}

/**
 * @param \Google_Service_Gmail $service
 * @param                       $userId
 * @param                       $messageId
 */
function scanMessage(Google_Service_Gmail $service, $userId, $messageId) {
    /** @var Google_Service_Gmail_Message $message */
    $message = $service->users_messages->get($userId, $messageId);

    /** @var Google_Service_Gmail_MessagePart $payload */
    $payload = $message->getPayload();

    /** @var Google_Service_Gmail_MessagePartHeader[] $headers */
    $headers = $payload->getHeaders();

    /** @var Google_Service_Gmail_MessagePartBody $body */
    $body = $payload->getBody();

    $interestingHeaders = ['Date', 'From', 'Subject', 'To'];
    $headersRaw = [];

    foreach ($headers as $header) {
        $headerName = $header->getName();
        $headerValue = $header->getValue();

        if (!in_array($headerName, $interestingHeaders)) {
            continue;
        }

        $headersRaw[$headerName] = $headerValue;
    }

    printf('Message ID: %s', $messageId);
    echo PHP_EOL;
    print_r($headersRaw);
    echo PHP_EOL;
}