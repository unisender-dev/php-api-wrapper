# unisender/api-wrapper
PHP wrapper for Unisender API requests.

English documentaion https://www.unisender.com/en/support/integration/api/

Русская документация https://www.unisender.com/ru/support/integration/api/

# Install
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
composer require unisender/api-wrapper
```

To use compression you have to install bz2 php extension.

# Changelog
v1.2    Since this version, you may specify platform - a new argument. We will track it in our database. This is usefull for us to keep close to all platforms which uses our api. It is not required argument.

v1.5    Since this version, we requrire at least PHP 5.6 to enable TLS 1.2 support. Now English is default locale for api request. 

v1.6    Updated all information into english, added new api-methods. Added support to change api request language.

# Basic usage example

```
<?php

require_once 'UnisenderApi.php';

use Unisender\ApiWrapper\UnisenderApi;

$platform = 'My E-commerce product v1.0';
$UnisenderApi = new UnisenderApi('api key here', 'UTF-8', 4, null, false, $platform);
$UnisenderApi->sendSms(
    ['phone' => 380971112233, 'sender' => 'Sender', 'text' => 'Hello World!']
);

```
