<?php

namespace OAuth1;

use \Guzzle\Http\Client as GuzzleClient;
use \Guzzle\Plugin\Oauth\OauthPlugin as GuzzleOAuth;
use \OAuth1\Token\Access as AccessToken;
use \OAuth1\Provider\ProviderInterface;
use \InvalidArgumentException;

class Client extends GuzzleClient
{
    protected $tokens;

    public function __construct($baseUrl = '', $config = null, ProviderInterface $provider = null, AccessToken $tokens = null)
    {
        if ($tokens) $this->setUserTokens($tokens);

        if ($provider) $this->setProvider($provider);

        parent::__construct($baseUrl, $config);
    }

    public function setUserTokens(AccessToken $tokens)
    {
        $this->tokens = $tokens;

        $this->setupOAuth();

        return $this;
    }

    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;

        $this->setupOAuth();

        return $this;
    }

    protected function setupOAuth()
    {
        if (isset($this->provider)) {

            $data = array(
                'consumer_key' => $this->provider->consumer->client_id,
                'consumer_secret' => $this->provider->consumer->secret,
                'signature_method' => $this->provider->signature->name
            );

            if (isset($this->tokens)) {
                $data['token'] = $this->tokens->access_token;
                $data['token_secret'] = $this->tokens->secret;
            }

            $oauth = new GuzzleOAuth($data);

            $this->addSubscriber($oauth);
        }
    }
}