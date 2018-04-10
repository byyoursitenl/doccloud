# ReportingCloud PHP Wrapper

[![Build Status](https://scrutinizer-ci.com/g/TextControl/txtextcontrol-reportingcloud-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/TextControl/txtextcontrol-reportingcloud-php/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TextControl/txtextcontrol-reportingcloud-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TextControl/txtextcontrol-reportingcloud-php/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/byyoursitenl/doccloud/v/stable)](https://packagist.org/packages/byyoursitenl/doccloud)
[![composer.lock](https://poser.pugx.org/byyoursitenl/doccloud/composerlock)](https://packagist.org/packages/byyoursitenl/doccloud)

This is the official PHP wrapper for DocCloud Web API. It is authored and supported by [Byyoursite BV](http://www.byyoursite.nl).

## Minimum Requirements

The DocCloud PHP wrapper requires **PHP 5.6** or newer. There are two technical reasons for this:

* All versions of PHP prior to PHP 5.6 have reached [end-of-life](http://php.net/eol.php) and should thus not be used in a production environment.

* The dependencies [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle) require PHP 5.6 or newer.

If your application is running in an older environment, it is highly advisable to update to a more current version of PHP.

If you are unable or unwilling to update your PHP installation, it is possible to use DocCloud by directly accessing the [Web API] without using this wrapper. In such cases, it is advisable to use the [curl](http://php.net/manual/en/book.curl.php) extension to make the API calls.


## Install Using Composer

The recommended way to install the DocCloud PHP wrapper in your project is using [Composer](http://getcomposer.org):

```bash
composer require byyoursitenl/doccloud:^1.0
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update the DocCloud PHP wrapper using Composer:

```bash
composer update
```

and for best auto-loading performance consequently execute:

```bash
composer dump-autoload --optimize
```


## Username and Password for Demos and Unit Tests

The DocCloud PHP wrapper ships with a number of sample applications (see directory `/demo`) and phpunit tests (see directory `/test`). The scripts in each of these directories require a username and password for DocCloud in order to be executed. So that your username and password are not made inadvertently publicly available via a public GIT repository, you will first need to specify them. There are two ways in which you can do this:

### Using PHP Constants:

```php
define('DOCCLOUD_USERNAME', 'your-username');
define('DOCCLOUD_PASSWORD', 'your-password');
```

### Using Environmental Variables (For Example in `.bashrc` or `.env`)

```bash
export DOCCLOUD_USERNAME='your-username'
export DOCCLOUD_PASSWORD='your-password'
```

Note, these instructions apply only to the demo scripts and phpunit tests. When you use DocCloud in your application, set credentials in your constructor, using the `setApiKey($apiKey)` or the `setUsername($username)` and `setPassword($password)` methods. For an example, see `/demo/instantiation.php`.
