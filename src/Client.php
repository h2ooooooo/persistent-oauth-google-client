<?php
/**
 * Created by PhpStorm.
 * User: aj
 * Date: 15/01/2019
 * Time: 13.10
 */

namespace jalsoedesign\PersistentOAuthGoogleClient;

use CLImax\Application;
use CLImax\DebugColour;
use jalsoedesign\CliClipboard\Clipboard;

/**
 * Class Client
 *
 * @package jalsoedesign\PersistentOAuthGoogleClient
 */
class Client {
    /** @var ClientConfiguration */
    protected $config;

    /** @var \Google_Client $client */
    protected $client;

    /** @var callable */
    protected $authCodeFromUrlCallback;

    /**
     * Client constructor.
     *
     * @param ClientConfiguration $config
     */
    public function __construct(ClientConfiguration $config) {
        $this->config = $config;
    }

    /**
     * Gets the google client and does all the hard work in relation to fetching access/refresh tokens etc.
     *
     * @param bool $forceRefresh Whether or not to force a refresh of the access token
     *
     * @return \Google_Client
     *
     * @throws \Google_Exception
     * @throws \Exception
     */
    public function getGoogleClient($forceRefresh = false) {
        if ($forceRefresh || $this->client === null) {
            $client = new \Google_Client();

            // Load client application name
            $applicationName = $this->config->getApplicationName();

            if (empty($applicationName)) {
                throw new \Exception(sprintf('Could not get application name from ClientConfiguration class'));
            }

            // Load client scopes
            $scopes = $this->config->getScopes();

            if (empty($scopes)) {
                throw new \Exception(sprintf('Could not get scopes from ClientConfiguration class'));
            }

            // Load client access type
            $accessType = $this->config->getAccessType();

            if (empty($accessType)) {
                throw new \Exception(sprintf('Could not get access type from ClientConfiguration class'));
            }

            // Load auth config path (oauth JSON file)
            $authConfigPath = $this->config->getAuthConfigPath();

            if (empty($authConfigPath)) {
                throw new \Exception(sprintf('Could not get auth config path from ClientConfiguration class'));
            }

            if (!file_exists($authConfigPath)) {
                throw new \Exception(sprintf('Could not find auth config at path %s', $authConfigPath));
            }

            // Verify auth code callback
            if (empty($this->authCodeFromUrlCallback)) {
                throw new \Exception(sprintf('Auth code from URL cannot be empty'));
            }

            if (!is_callable($this->authCodeFromUrlCallback)) {
                throw new \Exception(sprintf('Auth code from URL callback must be a callable'));
            }

            // Load token path
            $tokenPath = $this->config->getTokenPath();

            if (empty($tokenPath)) {
                throw new \Exception(sprintf('Could not get token path from ClientConfiguration class'));
            }

            // Set client properties
            $client->setApplicationName($applicationName);
            $client->setScopes($scopes);
            $client->setAuthConfig($authConfigPath);
            $client->setAccessType($accessType);

            // If we force a refresh we should delete the current token if it exists
            if ($forceRefresh && file_exists($tokenPath)) {
                unlink($tokenPath);
            }

            // Try to get client
            if (file_exists($tokenPath)) {
                // Assuming that the token exists, let's try to read it and use it
                $accessToken = json_decode(file_get_contents($tokenPath), true);

                if (empty($accessToken)) {
                    // Force a new access token fetch, as the JSON we have is invalid
                    return $this->getGoogleClient(true);
                }
            } else {
                // Create the Google auth URL
                $authUrl = $client->createAuthUrl();

                // Call the auth code callback to get the actual code to ask the user for their auth code
                $authCode = call_user_func($this->authCodeFromUrlCallback, $authUrl);

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                // Save the access token locally so we can reuse it later
                $this->saveAccessToken($accessToken);
            }

            try {
                // Set the access token on the Google client
                $client->setAccessToken($accessToken);
            } catch (\Exception $e) {
                // Assuming the token is invalid we force a refetch of one
                return $this->getGoogleClient(true);
            }

            // Assuming the access token is expired we'll use our refresh token to get a new access token
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                // We also overwrite our local access token
                $this->saveAccessToken($client->getAccessToken());
            }

            // Make sure we set the local client so we can return the same one again
            $this->client = $client;
        }

        // Return the client to do whatever you want with it
        return $this->client;
    }

    /**
     * Sets the callback to a CLImax application to ask the user interactively what auth code they got - usefor for CLI applications
     *
     * @param bool $setClipboard Whether or not to use CliClipboard to set the contents of the clipboard to the auth URL
     *
     * @param \CLImax\Application $application
     */
    public function setApplication(Application $application, $setClipboard = true) {
        $this->setAuthCodeFromUrlCallback(function($authUrl) use ($application, $setClipboard) {

            $application->verbose(sprintf('Info: In order to interact with the Google API we need to get access'));
            $application->verbose(sprintf('The only way to do this is to get an authentication token, which means you have to open your browser and sign in on the following URL. After you have done this you will be provided with an authentication code that you should paste back into the console'));

            $authUrlEnclosed = DebugColour::enclose($authUrl, DebugColour::WHITE, DebugColour::BLUE);
            $application->info(sprintf('Auth URL: %s', $authUrlEnclosed));

            if ($setClipboard) {
                Clipboard::instance()->set($authUrl);

                $application->verbose(sprintf('Auth URL has been copied to clipboard'));
            }

            return $application->question->ask('What is the verification code?');
        });
    }

    /**
     * Sets the auth code callback - whatever the callback returns should be the auth code output by Google
     *
     * @param callable $authCodeFromUrlCallback The callback to call as callback($authUrl)
     */
    public function setAuthCodeFromUrlCallback($authCodeFromUrlCallback) {
        if (empty($authCodeFromUrlCallback)) {
            throw new \BadMethodCallException(sprintf('Get auth code from URL callback must be defined'));
        }

        if (!is_callable($authCodeFromUrlCallback)) {
            throw new \BadMethodCallException(sprintf('Get auth code from URL callback must be a callable'));
        }

        $this->authCodeFromUrlCallback = $authCodeFromUrlCallback;
    }

    /**
     * Saves the user access token for future use
     *
     * @param array $accessToken The full access token array from Google
     *
     * @throws \Exception If the path isn't writable
     */
    protected function saveAccessToken($accessToken) {
        $tokenPath = $this->config->getTokenPath();

        $tokenPathDirectory = dirname($tokenPath);

        if (!file_exists($tokenPathDirectory)) {
            mkdir($tokenPathDirectory, 0777, true);
        }

        if (!is_writable($tokenPathDirectory)) {
            throw new \Exception(sprintf('Tokenpath directory %s is not writable', $tokenPathDirectory));
        }

        file_put_contents($tokenPath, json_encode($accessToken));
    }
}