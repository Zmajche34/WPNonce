# WPNonce

An object oriented implementation of the WordPress Nonce API.

# Requirements

  - PHP 7
  - WordPress 3.6.0 (This version introduces wp_unslash function, which is used in this package)

# Installation

It can be added to projects by adding this to composer.json:

```sh
{
    "repositories": [
        {
            "type": "vcs",
            "url" : "https://github.com/Zmajche34/WPNonce"
        }
    ],
    "require": {
        "zmajche34/wpnonce" : "1.0.*"
    }
}
```

# Testing

This package can be tested using composer.

```sh
$ cd WPNonce
$ composer install
$ vendor/bin/phpunit -c phpunit.xml.dist
```

# Development

## Initialization

The WPNonce object can be initialized by the following code:

```sh
$nonce_object = new WPNonce(
	"action",
	"request_name"
);
```

The first argument is an action, the second is a request name and the third optional is a life time, which is usually one day in seconds.

## Creation

The nonce can be created by the folllowing code:

```sh
$nonce = $nonce_object->create();
```

The nonce URL can be created by the folllowing code:

```sh
$url = "http://www.example.com/";
$nonce_url = $nonce_object->create_url($url);
```

The nonce field can be created by the folllowing code:

```sh
$nonce_field = $nonce_object->create_field();
```

## Verification

The nonce can be verified by the folllowing code:

```sh
$valid = $nonce_object->verify($nonce);
```

The nonce age can be given by the folllowing code:

```sh
$age = $nonce_object->age($nonce);
```

The result could be:
- false (if nonce is invalid)
- 1 (if nonce is "young" from 0 to half of lifetime)
- 2 (if nonce is "old" from half of lifetime to lifetime)
