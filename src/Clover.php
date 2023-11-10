<?php

namespace Stevelipinski\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Clover extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /*
    @var bool    
    */
    protected $sandbox = true;

    /*
    @var string    
    */
    protected $apiUrl;

    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['useSandbox'])) {
            $this->sandbox = $options['useSandbox'];
        }
        $this->apiUrl = $this->sandbox ? 'https://sandbox.dev.clover.com' : 'https://api.clover.com';
        parent::__construct($options, $collaborators);
    }

    /**
     * Get a Clover API URL, depending on path.
     *
     * @param  string $path
     * @return string
     */
    protected function getApiUrl($path)
    {
        return $this->apiUrl . '/' . $path;
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->getapiUrl('oauth/v2/authorize');
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getapiUrl('oauth/v2/token');
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getApiUrl('merchants/current/employees/current');
    }

    protected function getAccessTokenMethod()
    {
        return static::METHOD_GET;
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        // Clover does not seem to expose useful error information :(
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new CloverEmployee($response);
    }
}
