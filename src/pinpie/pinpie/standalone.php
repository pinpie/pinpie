<?php


spl_autoload_register(function ($class) {
  $namespace = substr($class, 0, 15);
  $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', '/', substr($class, 16)) . '.php';

  if ($namespace !== 'pinpie\\PinPIE') {
    return false;
  }

  if (!file_exists($file)) {
    return false;
  }

  require_once $file;
  return true;
});

class_alias('\pinpie\pinpie\PinPIE', 'PinPIE');
PinPIE::newInstance();