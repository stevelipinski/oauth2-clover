<?php

namespace Stevelipinski\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Stevelipinski\OAuth2\Client\Provider\Clover;
use Mockery;
use PHPUnit\Framework\TestCase;

class CloverTest extends TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Clover([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none'
        ]);
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/v2/token', $uri['path']);
        $this->assertContains('clover.com', $uri['host']);
        $this->assertContains('sandbox', $uri['host']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = new AccessToken(['access_token' => 'fake']);

        $url = $this->provider->getResourceOwnerDetailsUrl($token);

        $this->assertContains('employees/current', $url);
    }

    public function testUserData()
    {
        $response = json_decode('{"id": "ABCDE", "name": "mock_name", "email": "mock_email", "role": "EMPLOYEE"}', true);

        $provider = Mockery::mock('Stevelipinski\OAuth2\Client\Provider\Clover[fetchResourceOwnerDetails]')
            ->shouldAllowMockingProtectedMethods();

        $provider->shouldReceive('fetchResourceOwnerDetails')
            ->times(1)
            ->andReturn($response);

        $token = Mockery::mock('League\OAuth2\Client\Token\AccessToken');
        $user = $provider->getResourceOwner($token);

        $this->assertInstanceOf('League\OAuth2\Client\Provider\ResourceOwnerInterface', $user);

        $this->assertEquals('ABCDE', $user->getId($token));
        $this->assertEquals('mock_name', $user->getName());
        $this->assertEquals('mock_email', $user->getEmail());
        $this->assertEquals('EMPLOYEE', $user->getRole());
        $this->assertTrue($user->isEmployee());
        $this->assertFalse($user->isManager());
        $this->assertFalse($user->isAdmin());

        $user = $user->toArray();

        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('role', $user);
    }
}
