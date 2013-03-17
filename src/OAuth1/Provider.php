<?php

namespace OAuth1;

use \OAuth1\Token;
use \OAuth1\Token\Request as RequestToken;
use \OAuth1\Token\Access as AccessToken;
use \OAuth1\Request\Token as TokenRequest;
use \OAuth1\Request\Authorize as AuthorizeRequest;
use \OAuth1\Request\Access as AccessRequest;
use \OAuth1\Request\Resource as ResourceRequest;
use \OAuth1\Consumer;
use \OAuth1\Signature;
use \Exception;

/**
 * OAuth Provider
 *
 * @package    CodeIgniter/OAuth
 * @category   Provider
 * @author     Phil Sturgeon
 * @copyright  (c) 2012 HappyNinjas Ltd
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */

class Provider
{

    /**
     * @var  string  provider name
     */
    public $name;

    /**
     * @var  string  signature type
     */
    protected $signature = 'HMAC-SHA1';

    /**
     * @var  string  uid key name
     */
    public $uid_key = 'uid';

    /**
     * @var  array  additional request parameters to be used for remote requests
     */
    protected $params = array();

    /**
     * @var  string  default scope (useful if a scope is required for user info)
     */
    protected $scope;

    /**
     * @var  string  scope separator, most use "," but some like Google are spaces
     */
    public $scope_seperator = ',';

    protected $token;

    /**
     * Overloads default class properties from the options.
     *
     * Any of the provider options can be set here:
     *
     * Type      | Option        | Description                                    | Default Value
     * ----------|---------------|------------------------------------------------|-----------------
     * mixed     | signature     | Signature method name or object                | provider default
     *
     * @param   array   provider options
     * @return void
     */
    public function __construct(array $options = null)
    {
        if (isset($options['signature'])) {
            // Set the signature method name or object
            $this->signature = $options['signature'];
        }

        if ( ! is_object($this->signature)) {
            // Convert the signature name into an object
            $class = str_replace('-', '', $this->signature);
            $class = "\OAuth1\Signature\\$class";
            $this->signature = new $class;
        }

        $this->consumer = new Consumer($options);
    }

    /**
     * Return the value of any protected class variable.
     *
     *     // Get the provider signature
     *     $signature = $provider->signature;
     *
     * @param   string  variable name
     * @return mixed
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * Ask for a request token from the OAuth provider.
     *
     *     $token = $provider->request_token($consumer);
     *
     * @param   Consumer  consumer
     * @param   array           additional request parameters
     * @return Token_Request
     * @uses    Request_Token
     */
    public function requestToken($redirect_url = null, array $params = null)
    {
        $redirect_url = $redirect_url ?: $this->consumer->redirect_url;
        $scope = is_array($this->consumer->scope) ? implode($this->consumer->scope_seperator, $this->consumer->scope) : $this->consumer->scope;

        // Create a new GET request for a request token with the required parameters
        $request = new TokenRequest('GET', $this->requestTokenUrl(), array(
            'oauth_consumer_key' => $this->consumer->client_id,
            'oauth_callback'     => $redirect_url,
            'scope'              => $scope
        ));

        if ($params) {
            // Load user parameters
            $request->params($params);
        }

        // Sign the request using only the consumer, no token is available yet
        $request->sign($this->signature, $this->consumer
            ->scope($scope)
            ->callback($redirect_url)
        );

        // Create a response from the request
        $response = $request->execute();

        // Store this token somewhere useful
        return new RequestToken(array(
            'access_token'  => $response->param('oauth_token'),
            'secret' => $response->param('oauth_token_secret'),
        ));
    }

    public function isCallback()
    {
        return isset($_REQUEST['oauth_token']);
    }

    public function validateCallback(AccessToken $token)
    {
        if ($token->access_token === $_REQUEST['oauth_token']) {

            if ( ! isset($_REQUEST['oauth_verifier'])) {
                throw new Exception('OAuth verifier was not found in request');
            }

            $token->verifier($_REQUEST['oauth_verifier']);

            $this->tokens = $this->accessToken($token);

            return true;

        } else {
            throw new Exception('Token mismatch');
        }
    }

    public function getUserTokens()
    {
        return isset($this->tokens) ? $this->tokens : false;
    }

    public function call($method = 'GET', $url, array $params = array())
    {
        // Create a new GET request with the required parameters
        $request = new ResourceRequest($method, $url, array_merge(array(
            'oauth_consumer_key' => $this->consumer->client_id,
            'oauth_token' => $this->token->access_token
        ), $params));

        // Sign the request using the consumer and token
        $request->sign($this->signature, $this->consumer, $this->token);

        return $request->execute();
    }

    /**
     * Get the authorization URL for the request token.
     *
     *     Response::redirect($provider->authorize_url($token));
     *
     * @param   Token_Request  token
     * @param   array                additional request parameters
     * @return string
     */
    public function authorize(RequestToken $token, array $params = null)
    {
        // Create a new GET request for a request token with the required parameters
        $request = new AuthorizeRequest('GET', $this->authorizeUrl(), array(
            'oauth_token' => $token->access_token,
        ));

        if ($params) {
            // Load user parameters
            $request->params($params);
        }

        return $request->asUrl();
    }

    /**
     * Exchange the request token for an access token.
     *
     *     $token = $provider->access_token($consumer, $token);
     *
     * @param   Consumer       consumer
     * @param   Token_Request  token
     * @param   array                additional request parameters
     * @return Token_Access
     */
    public function accessToken(RequestToken $token, array $params = null)
    {
        // Create a new GET request for a request token with the required parameters
        $request = new AccessRequest('GET', $this->accessTokenUrl(), array(
            'oauth_consumer_key' => $this->consumer->client_id,
            'oauth_token'        => $token->access_token,
            'oauth_verifier'     => $token->verifier,
        ));

        if ($params) {
            // Load user parameters
            $request->params($params);
        }

        // Sign the request using only the consumer, no token is available yet
        $request->sign($this->signature, $this->consumer, $token);

        // Create a response from the request
        $response = $request->execute();

        // Store this token somewhere useful
        return new AccessToken(array(
            'access_token'  => $response->param('oauth_token'),
            'secret' => $response->param('oauth_token_secret'),
            'uid' => $response->param($this->uid_key) ? $response->param($this->uid_key) : $_REQUEST[$this->uid_key],
        ));
    }

} // End Provider
