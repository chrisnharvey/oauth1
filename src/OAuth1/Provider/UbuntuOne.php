<?php

namespace OAuth1\Provider;

use \OAuth1\Request\Resource;

class UbuntuOne extends \OAuth1\Provider
{
    public $name = 'ubuntuone';

    public $signature = 'PLAINTEXT';

    public function requestTokenUrl()
    {
        return 'https://one.ubuntu.com/oauth/request/';
    }

    public function authorizeUrl()
    {
        return 'https://one.ubuntu.com/oauth/authorize/';
    }

    public function accessTokenUrl()
    {
        return 'https://one.ubuntu.com/oauth/access/';
    }

    public function getUserInfo()
    {
        // Get user data
    }
}
