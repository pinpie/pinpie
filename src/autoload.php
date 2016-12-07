<?php


spl_autoload_register(function ($class) {
  $namespace = substr($class, 0, 14);
  $className = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 14));
  $file = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';

  if ($namespace !== 'pinpie\\pinpie\\') {
    return false;
  }

  if (!file_exists($file)) {
    return false;
  }

  require_once $file;
  return true;
});

