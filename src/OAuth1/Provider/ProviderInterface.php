<?php

namespace OAuth1\Provider;

interface ProviderInterface
{
    public function requestTokenUrl();
    public function authorizeUrl();
    public function accessTokenUrl();
    public function getUserInfo();
}