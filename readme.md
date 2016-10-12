#PinPIE - when PHP Is Enough

[![PinPIE](https://img.shields.io/badge/PHP-PinPIE-brightgreen.svg?style=flat-square)](http://pinpie.ru/)
[![Build Status](http://img.shields.io/travis/pinpie/pinpie.svg?style=flat-square)](https://travis-ci.org/pinpie/pinpie)
[![Latest Stable Version](https://img.shields.io/packagist/v/pinpie/pinpie.svg?style=flat-square)](https://packagist.org/packages/pinpie/pinpie)
[![Total Downloads](https://img.shields.io/packagist/dt/pinpie/pinpie.svg?style=flat-square)](https://packagist.org/packages/pinpie/pinpie)
[![License](https://img.shields.io/packagist/l/pinpie/pinpie.svg?style=flat-square)](https://packagist.org/packages/pinpie/pinpie)


PinPIE это лёгкий движок для небольших сайтов.

PinPIE спроектирован так чтобы выдавать страницу за считанные миллисекунды даже на дешёвом VPS/VDS хостинге. Но его можно использовать и на шаред хостинге.

PinPIE хранит весь контент в файлах "*.php", которые кэшируются опкод-кэшером, что позволяет инклудить страницы, сниппеты и чанки просто молниеносно.

В PinPIE используются теги. Теги можно кэшировать. Кэширование легко включается и выключается отдельно для каждого тега. Управление кэшированием тегов очень понятное и простое. При обновлении файлов PinPIE автоматически перекэширует то, что изменилось.

[Подробнее на сайте PinPIE](http://pinpie.ru/ru/manual)

##Installation
###Requirements
PHP 5.4 or later
###Option 1: Install via Composer
```
composer require "pinpie/pinpie"
composer install
```
###Option 2: Download from GitHub
 * Скачать https://github.com/pinpie/pinpie/archive/dev.zip
 * Подключить /src/autoload.php
 * Подробнее в [доке](http://pinpie.ru/ru/manual) 


[Подробнее на сайте PinPIE](http://pinpie.ru/ru/manual)