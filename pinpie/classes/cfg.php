<?php

class CFG {

  // descriptions are in ::ReadConf() func
  public static
    $random_stuff = null,
    $conf = null,
    $pages_folder = null,
    $page_not_found = null,
    $databases = null,
    $static_servers = null,
    $theme = null,
    $theme_path = null,
    $theme_url = null,
    $showtime = null,
    $route_to_parent = null,
    $log = null,
    $pinpie = null,
    $debug = null;

  /**
   * Internal method to read configuration file.
   */
  public static function ReadConf() {
    $pinpie = []; //settings for PinPIE
    $conf = []; //you can put some custom setting here
    $databases = []; //to store database settings
    $static_servers = []; //list here static content servers addresses if you want to use them
    $showtime = false; //show page generating time
    $random_stuff = false; //very important to generate long random string for every your site. Please, press as many keys on your keyboard as you can. Or just use online password generators.
    $debug = false; //enables PinPIE::report() output. Use it to enable your own debug mode. Globally available through CFG::$debug.

    //Loading defaults
    $pinpie = [
      'cache type' => 'files',
      'cache rules' => [
        'default' => ['ignore url' => false, 'ignore query params' => []],
        200 => ['ignore url' => false, 'ignore query params' => []],
        404 => ['ignore url' => true, 'ignore query params' => []]
      ],
      'cache hash algo' => 'sha1',
      'cache forever time' => PHP_INT_MAX,
      'chunks folder' => ROOT . DS . 'chunks',
      'chunks realpath check' => true,
      'codepage' => 'utf-8',
      'log' => [
        'path' => 'pin.log',
        'show' => false,
      ],
      'static folder' => ROOT,
      'static gzip level' => 5,
      'static gzip types' => ['js', 'css'],
      'static minify types' => ['js', 'css'],
      'static minify function' => false,
      'static dimensions types' => ['img'],
      'static dimensions function' => false,
      'static draw function' => false,
      'static realpath check' => true,
      'pages folder' => ROOT . DS . 'pages',
      'pages realpath check' => true,
      'page not found' => 'index.php',
      'route to parent' => 1, //read doc. if exact file not found, instead of 404, PinPIE will try to route request to nearest existing parent entry in url. Default is 1, it means PinPIE will handle "site.com/url" and "site.com/url/" as same page.
      'site url' => $_SERVER['SERVER_NAME'],
      'snippets folder' => ROOT . DS . 'snippets',
      'snippets realpath check' => true,
      'templates folder' => ROOT . DS . 'templates',
      'template function' => false,
      'template clear vars after use' => false,
      'templates realpath check' => true,
      'working folder' => ROOT,
      'preinclude' => ROOT . DS . 'preinclude.php',
      'postinclude' => ROOT . DS . 'postinclude.php',
    ];
    //Reading file and overwriting defaults
    $config = ROOT . DS . 'config' . DS . basename($_SERVER['SERVER_NAME']) . '.php';
    if (file_exists($config)) {
      include($config);
    } else {
      echo 'config for ' . basename($_SERVER['SERVER_NAME']) . ' not found at ' . $config;
      exit(); //no config
    }

    self::$conf = $conf; //you can use that array to store settings for your own scripts
    self::$databases = $databases;
    self::$static_servers = $static_servers;
    self::$showtime = $showtime;
    self::$random_stuff = $random_stuff;
    self::$pinpie = $pinpie;
    self::$debug = $debug;
  }

}

CFG::ReadConf();

