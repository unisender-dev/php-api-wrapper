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

#Usage

```
<?php

$platform = 'My E-commerce product v1.0';
$UnisenderApi = new UnisenderApi('api key here', 'UTF-8', 4, null, false, $platform);
$UnisenderApi->sendSms(
    ['phone' => 380971112233, 'sender' => 'Sender', 'text' => 'Hello World!']
);

```