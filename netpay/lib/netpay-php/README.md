# NetPay PHP bindings

[![Build Status](https://travis-ci.org/netpay-ecommerce/netpay-php.svg?branch=master)](https://travis-ci.com/netpay-ecommerce/netpay-php)
[![Latest Stable Version](https://poser.pugx.org/netpay/netpay-php/v/stable.svg)](https://packagist.org/packages/netpay/netpay-php)
[![Total Downloads](https://poser.pugx.org/netpay/netpay-php/downloads.svg)](https://packagist.org/packages/netpay/netpay-php)
[![License](https://poser.pugx.org/netpay/netpay-php/license.svg)](https://packagist.org/packages/netpay/netpay-php)
[![Code Coverage](https://coveralls.io/repos/netpay/netpay-php/badge.svg?branch=master)](https://coveralls.io/r/netpay/netpay-php?branch=master)

You can sign up for a NetPay account at https://developers.netpay.com.mx.

## Minimum Requirements

PHP 5.4.0 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require netpaymx/netpay-php
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/netpay-ecommerce/netpay-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/netpay-php/init.php');
```

## Getting Started

Sample code:

```php
require_once ('../init.php');

use \NetPay\Config;

try {
    $data = array(
        'userName' => Config::USER_NAME,
        'password' => Config::PASS,
    );

    $login = \NetPay\Api\Login::post($data);

    if ($jwt === false) {
        print_r($login);
        return false;
    }
    
    echo $login['result']['token'];
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
```

## Documentation

Please see https://developers.netpay.com.mx for up-to-date documentation.

## Custom Request Timeouts

*NOTE:* We do not recommend decreasing the timeout for non-read-only calls (e.g. charge creation), since even if you locally timeout, the request on NetPay's side can still complete.

File lib/Config.php

```php
//-- General settings
const CURLOPT_TIMEOUT = 40; //Timeout in seconds
```

File lib/NetPay/Api/Curl.php

```php
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, strlen($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::CURLOPT_TIMEOUT);
curl_setopt($ch, CURLOPT_TIMEOUT, Config::CURLOPT_TIMEOUT);
```