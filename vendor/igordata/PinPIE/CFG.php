<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-08-16
 * Time: 21:51
 */

namespace igordata\PinPIE;

class CFG {

  // descriptions are in ::ReadConf() func
  public
    $cache = null,
    $conf = null,
    $databases = null,
    $static_servers = null,
    $showtime = null,
    $random_stuff = null,
    /** @var PP|null */
    $pinpie = null,
    $debug = null;

  public function __construct($pinpie) {
    $this->pinpie = $pinpie;
  }

  /**
   * Internal method to read configuration file.
   */
  public function readConf($config) {
    $pinpie = []; //settings for PinPIE
    $cache = [];
    $conf = []; //you can put some custom setting here
    $databases = []; //to store database settings
    $static_servers = []; //list here static content servers addresses if you want to use them
    $showtime = false; //show page generating time
    $random_stuff = false; //very important to generate long random string for every your site. Please, press as many keys on your keyboard as you can. Or just use online password generators.
    $debug = false; //enables PinPIE::report() output. Use it to enable your own debug mode. Globally available through CFG::$debug.

    //Loading defaults
    $pinpie = [
      'tags' => [
        '' => '\igordata\PinPIE\Tags\Chunk',
        '$' => '\igordata\PinPIE\Tags\Snippet',
        '=' => '\igordata\PinPIE\Tags\Constant',
        '@' => '\igordata\PinPIE\Tags\Command',
        '%' => '\igordata\PinPIE\Tags\Staticon',
      ],
      'cache class' => false,
      'cache rules' => [
        'default' => ['ignore url' => false, 'ignore query params' => []],
        200 => ['ignore url' => false, 'ignore query params' => []],
        404 => ['ignore url' => true, 'ignore query params' => []]
      ],
      'cache forever time' => PHP_INT_MAX,
      'chunks folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'chunks',
      'chunks realpath check' => true,
      'codepage' => 'utf-8',
      'log' => [
        'path' => 'pin.log',
        'show' => false,
      ],
      'static folder' => $this->pinpie->root,
      'static gzip level' => 5,
      'static gzip types' => ['js', 'css'],
      'static minify types' => ['js', 'css'],
      'static minify function' => false,
      'static dimensions types' => ['img'],
      'static dimensions function' => false,
      'static draw function' => false,
      'static realpath check' => true,
      'pages folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'pages',
      'pages realpath check' => true,
      'page not found' => 'index.php',
      'route to parent' => 1, //read doc. if exact file not found, instead of 404, PinPIE will try to route request to nearest existing parent entry in url. Default is 1, it means PinPIE will handle "site.com/url" and "site.com/url/" as same page.
      'site url' => $_SERVER['SERVER_NAME'],
      'snippets folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'snippets',
      'snippets realpath check' => true,
      'templates folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'templates',
      'template function' => false,
      'template clear vars after use' => false,
      'templates realpath check' => true,
      'working folder' => $this->pinpie->root,
      'preinclude' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'preinclude.php',
      'postinclude' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'postinclude.php',
    ];

    //Reading file and overwriting defaults
    if (file_exists($config)) {
      include($config);
    } else {
      echo 'config for ' . basename($_SERVER['SERVER_NAME']) . ' not found at ' . $config;
      exit(); //no config
    }

    if (isset($pinpie['cache type'])) {
      if (empty($pinpie['cache class'])) {
        $pinpieCacheClasses = [
          'disabled' => '\igordata\PinPIE\Cachers\Disabled',
          'files' => '\igordata\PinPIE\Cachers\Files',
          'APC' => '\igordata\PinPIE\Cachers\APC',
          'Memcache' => '\igordata\PinPIE\Cachers\Memcache',
        ];
        if (isset($pinpieCacheClasses[$pinpie['cache type']])) {
          $pinpie['cache class'] = $pinpieCacheClasses[$pinpie['cache type']];
        }
      }
    }

    $this->cache = $cache;
    $this->conf = $conf; //you can use that array to store settings for your own scripts
    $this->databases = $databases;
    $this->static_servers = $static_servers;
    $this->showtime = $showtime;
    $this->random_stuff = $random_stuff;
    $this->pinpie = $pinpie;
    $this->debug = $debug;
  }

}


