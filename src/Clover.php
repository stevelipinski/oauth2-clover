<?php

namespace Stevelipinski\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Grant\AbstractGrant;
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
        return $this->getApiUrl('oauth/v2/authorize');
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        if (isset($params['grant_type']) && $params['grant_type'] = 'refresh_token') {
            return $this->getApiUrl('oauth/v2/refresh');
        } else {
            return $this->getApiUrl('oauth/v2/token');
        }
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getApiUrl('merchants/current/employees/current');
    }

    protected function getAccessTokenOptions(array $params)
    {
        $options = ['headers' => ['content-type' => 'application/json']];

        if ($this->getAccessTokenMethod() === self::METHOD_POST) {
            $options['body'] = $this->getAccessTokenBody($params);
        }

        return $options;
    }

    // Clover uses JSON body instead of urlencoded form
    protected function getAccessTokenBody(array $params)
    {
        return json_encode($params);
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400)
        {
            $data = (is_array($data)) ? $data : json_decode($data, true);
            $error = 'unknown';
            if (isset($data['error']))
            {
                $error = $data['error'];
            }
            if (isset($data['errors']))
            {
                $error = print_r($data['errors'], true);
            }
            throw new IdentityProviderException($error, $response->getStatusCode(), $data);
        }
    }

    // Clover sends access_token_expiration instead of expires. 
    protected function prepareAccessTokenResponse(array $result)
    {
        if (isset($result['access_token_expiration']) && !isset($result['expires'])) {
            $result['expires'] = $result['access_token_expiration'];
        }
        return parent::prepareAccessTokenResponse($result);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new CloverEmployee($response);
    }
}
