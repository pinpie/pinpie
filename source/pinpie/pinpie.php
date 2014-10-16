<?php

define('PIN_TIME_START', microtime(true));
define('PIN_MEMORY_START', memory_get_usage());
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(str_replace('\\', '/', dirname($_SERVER["SCRIPT_FILENAME"])), DS));
define('PINDIR', rtrim(str_replace('\\', '/', __DIR__), DS));
include PINDIR . DS . 'throw.php';
include PINDIR . DS . 'classes' . DS . 'cfg.class.php';
include PINDIR . DS . 'classes' . DS . 'pinpie.class.php';
include PINDIR . DS . 'classes' . DS . 'staticon.class.php';
include PINDIR . DS . 'classes' . DS . 'cache.class.' . basename(CFG::$pinpie['cache type']) . '.php';

PinPIE::$times['PinPIE class is loaded'] = microtime(true);


$preinclude = ROOT . DS . 'preinclude.php';
$postinclude = ROOT . DS . 'postinclude.php';
PinPIE::Init();
if (file_exists($preinclude)) {
    include $preinclude;
}
$path = rtrim(CFG::$pinpie['pages folder'], DS) . DS . trim(PinPIE::$document, DS);
$path = PinPIE::checkPathIsInFolder($path, CFG::$pinpie['pages folder']);
if ($path !== false AND file_exists($path)) {
    include $path;
}
PinPIE::postincludes();
if (file_exists($postinclude)) {
    include $postinclude;
}


//for curious boys:
if (CFG::$showtime) {
    echo number_format((microtime(true) - PIN_TIME_START) * 1000, 3, '.', '') . "ms";
}
//echo 'Memory: ' . (memory_get_usage() - PIN_MEMORY_START) . ' Peak: ' . memory_get_peak_usage();


