<?php


namespace jalsoedesign\PersistentOAuthGoogleClient;

/**
 * Class ClientConfiguration
 *
 * @package jalsoedesign\PersistentOAuthGoogleClient
 */
class ClientConfiguration {
    const ACCESS_TYPE_ONLINE = 'online'; // A refresh token will NOT be returned and user interaction is REQUIRED
    const ACCESS_TYPE_OFFLINE = 'offline'; // A refresh token will be returned so we can call it again without user interaction

    /** @var string */
    protected $applicationName = 'SimpleGoogleClient';

    /** @var string */
    protected $accessType = ClientConfiguration::ACCESS_TYPE_OFFLINE;

    /** @var array */
    protected $scopes = [];

    /** @var string */
    protected $authConfigPath;

    /** @var string */
    protected $tokenPath;

    /**
     * Sets the application name to forward to the Google client
     *
     * @param string $applicationName
     */
    public function setApplicationName($applicationName) {
        $this->applicationName = $applicationName;
    }

    /**
     * Gets the application name to forward to the Google client
     *
     * @return string
     */
    public function getApplicationName() {
        return $this->applicationName;
    }

    /**
     * Sets the path to where the user access token should be saved on successful connection
     *
     * @param string $tokenPath
     */
    public function setTokenPath($tokenPath) {
        $this->tokenPath = $tokenPath;
    }

    /**
     * Gets the path to where the user access token should be saved on successful connection
     *
     * @return string
     */
    public function getTokenPath() {
        return $this->tokenPath;
    }

    /**
     * Sets the application scopes to forward to the Google client
     *
     * @param array $scopes
     */
    public function setScopes($scopes) {
        $this->scopes = $scopes;
    }

    /**
     * Gets the application scopes to forward to the Google client
     *
     * @return array
     */
    public function getScopes() {
        return $this->scopes;
    }

    /**
     * Sets the path to the auth config (this is the OAuth json file downloaded from Google when creating the credentials)
     *
     * @param string $authConfigPath
     */
    public function setAuthConfigPath($authConfigPath) {
        $this->authConfigPath = $authConfigPath;
    }

    /**
     * Gets the path to the auth config  (this is the OAuth json file downloaded from Google when creating the credentials)
     *
     * @return string
     */
    public function getAuthConfigPath() {
        return $this->authConfigPath;
    }

    /**
     * Sets the access type (offline / online) to control whether or not refresh tokens are returned
     *
     * @param string $accessType One of the ClientConfiguration::ACCESS_TYPE_* constants
     */
    public function setAccessType($accessType) {
        $this->accessType = $accessType;
    }

    /**
     * Gets the access type (offline / online) to control whether or not refresh tokens are returned
     *
     * @return string
     */
    public function getAccessType() {
        return $this->accessType;
    }
}