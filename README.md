# Clover Provider for OAuth 2.0 Client

[![Source Code](https://img.shields.io/badge/source-stevelipinski/oauth2--clover-blue.svg?style=flat-square)](https://github.com/stevelipinski/oauth2-clover)
[![Latest Version](https://img.shields.io/github/release/stevelipinski/oauth2-clover.svg?style=flat-square)](https://github.com/stevelipinski/oauth2-clover/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/stevelipinski/oauth2-clover/blob/master/LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/stevelipinski/oauth2-clover/continuous-integration.yml?label=CI&logo=github&style=flat-square)](https://github.com/stevelipinski/oauth2-clover/actions?query=workflow%3ACI)
[![Codecov Code Coverage](https://img.shields.io/codecov/c/gh/stevelipinski/oauth2-clover?label=codecov&logo=codecov&style=flat-square)](https://codecov.io/gh/stevelipinski/oauth2-clover)

This package provides [Clover OAuth 2.0](https://demo1.dev.clover.com/docs/oauth) support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require stevelipinski/oauth2-clover
```

## Usage

Usage is the same as The League's OAuth client, using `Stevelipinski\OAuth2\Client\Provider\Clover` as the provider.

### Authorization Code Flow

```php
$provider = new Stevelipinski\OAuth2\Client\Provider\Clover([
    'clientId'     => '{clover-client-id}',
    'clientSecret' => '{clover-client-secret}',
    'marketPrefix' => '{clover-market-prefix}',
    'redirectUri'  => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->state;
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $userDetails = $provider->getUserDetails($token);

        // Use these details to create a new profile
        printf('Hello %s!', $userDetails->name);

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->accessToken;

}
```
