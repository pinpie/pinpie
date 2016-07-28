<?php
define('PIN_TIME_START', microtime(true));
define('PIN_MEMORY_START', memory_get_peak_usage());
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(str_replace('\\', '/', dirname($_SERVER["SCRIPT_FILENAME"])), DS));
define('PINDIR', rtrim(str_replace('\\', '/', __DIR__), DS));

include PINDIR . DS . 'throw.php';
include PINDIR . DS . 'classes' . DS . 'cfg.php';
include PINDIR . DS . 'classes' . DS . 'pinpie.php';
include PINDIR . DS . 'classes' . DS . 'staticon.php';
include PINDIR . DS . 'classes' . DS . 'cache.php';
include PINDIR . DS . 'classes' . DS . 'cacher.php';

if (CFG::$pinpie['static dimensions types']) {
  include PINDIR . DS . 'classes' . DS . 'fastimage.php';
}

PinPIE::$times['PinPIE classes are loaded'] = microtime(true);
PinPIE::Init();

if (!empty(CFG::$pinpie['preinclude']) AND file_exists(CFG::$pinpie['preinclude'])) {
  include CFG::$pinpie['preinclude'];
}

$path = rtrim(CFG::$pinpie['pages folder'], DS) . DS . trim(PinPIE::$document, DS);
if (CFG::$pinpie['pages realpath check']) {
  $path = PinPIE::checkPathIsInFolder($path, CFG::$pinpie['pages folder']);
}

if ($path !== false AND file_exists($path)) {
  include $path;
}
PinPIE::render();

if (!empty(CFG::$pinpie['postinclude']) AND file_exists(CFG::$pinpie['postinclude'])) {
  include CFG::$pinpie['postinclude'];
}

if (CFG::$showtime) {
  echo number_format((microtime(true) - PIN_TIME_START) * 1000, 3, '.', '') . "ms";
}



