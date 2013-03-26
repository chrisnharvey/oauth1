<?php

namespace OAuth1\Provider;

use \OAuth1\Provider;
use \OAuth1\Token;
use \OAuth1\Token\Access;
use \OAuth1\Consumer;
use \OAuth1\Client;
use \Exception;

class Twitter extends Provider implements ProviderInterface
{

    public $name = 'twitter';

    public $uid_key = 'user_id';

    public function requestTokenUrl()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function authorizeUrl()
    {
        return 'https://api.twitter.com/oauth/authorize';
    }

    public function accessTokenUrl()
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    public function getUserInfo()
    {
        if (! $this->tokens instanceof Access) {
            throw new Exception('Tokens must be an instance of Access');
        }

        $request = new Client('http://api.twitter.com/1.1');
        $request->setUserTokens($this->tokens)
            ->setProvider($this);

        $response = $request->get("users/lookup.json?user_id={$this->tokens->uid}")->send()->json();

        $user = current($response);

        // Create a response from the request
        return array(
            'uid' => $this->tokens->uid,
            'nickname' => $user['screen_name'],
            'name' => $user['name'] ? $user['name'] : $user['screen_name'],
            'location' => $user['location'],
            'image' => $user['profile_image_url'],
            'description' => $user['description'],
            'urls' => array(
              'Website' => $user['url'],
              'Twitter' => 'http://twitter.com/'.$user['screen_name'],
            ),
        );
    }

} // End Provider_Twitter
