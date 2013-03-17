# OAuth 1.0 Composer Package

Authorize users with your application using multiple OAuth 1 providers.

## Supported Providers

- Dropbox
- Flickr
- LinkedIn
- Tumblr
- Twitter
- UbuntuOne

## Usage Example

In this example we will authenticate the user using Twitter.

```php
session_start();

$oauth = \OAuth1\Provider\Twitter(array(
	'id' => 'CLIENT_ID',
	'secret' => 'CLIENT_SECRET',
	'redirect_url' => 'URL_TO_THIS_PAGE'
));

if ($oauth->isCallback()) {
	$oauth->validateCallback(unserialize($_SESSION['token']))
} else {
	$token = $oauth->requestToken();

	$_SESSION['token'] = serialize($token);

	$url = $oauth->authorize($token);

	header("Location: {$url}");
	exit;
}

// Tokens
print_r($oauth->getUserTokens());

// User data
print_r($oauth->getUserInfo());
```

If all goes well you should see a dump of the users tokens and data.

## Contribute

1. Check for open issues or open a new issue for a feature request or a bug
2. Fork [the repository][] on Github to start making your changes to the
    `develop` branch (or branch off of it)
3. Write a test which shows that the bug was fixed or that the feature works as expected
4. Send a pull request and bug me until I merge it

[the repository]: https://github.com/chrisnharvey/oauth1
