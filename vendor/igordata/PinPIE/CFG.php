<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-08-16
 * Time: 21:51
 */

namespace igordata\PinPIE;

class CFG {

  // descriptions are in ReadConf()
  public
    $cache = null,
    $oth = null,
    $databases = null,
    $static_servers = null,
    $showtime = null,
    $random_stuff = null,
    /** @var PP|null */
    $pinpie = null,
    $debug = null,
    $tags = [];

  public function __construct($pinpie) {
    $this->pinpie = $pinpie;
  }

  /**
   * Internal method to read configuration file.
   */
  public function readConf($config) {
    $cache = []; // settings for current cacher
    $oth = []; //you can put some custom setting here
    $databases = []; //to store database settings
    $static_servers = []; //list here static content servers addresses if you want to use them
    $showtime = false; //show page generating time
    $random_stuff = false; //very important to generate long random string for every your site. Please, press as many keys on your keyboard as you can. Or just use online password generators.
    $debug = false; //enables PinPIE::report() output. Use it to enable your own debug mode. Globally available through CFG::$debug.

    //Loading defaults
    $pinpie = [
      'cache class' => false,
      'cache rules' => [
        'default' => ['ignore url' => false, 'ignore query params' => []],
        200 => ['ignore url' => false, 'ignore query params' => []],
        404 => ['ignore url' => true, 'ignore query params' => []]
      ],
      'cache forever time' => PHP_INT_MAX,
      'codepage' => 'utf-8',
      'log' => [
        'path' => 'pin.log',
        'show' => false,
      ],
      'page not found' => 'index.php',
      'route to parent' => 1, //read doc. if exact file not found, instead of 404, PinPIE will try to route request to nearest existing parent entry in url. Default is 1, it means PinPIE will handle "site.com/url" and "site.com/url/" as same page.
      'site url' => $_SERVER['SERVER_NAME'],
      'templates folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'templates',
      'template function' => false,
      'template clear vars after use' => false,
      'templates realpath check' => true,
      'preinclude' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'preinclude.php',
      'postinclude' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'postinclude.php',
    ];

    $tags = [
      '' => [
        'class' => '\igordata\PinPIE\Tags\Chunk',
        'folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'chunks',
        'realpath check' => true,
      ],
      '$' => [
        'class' => '\igordata\PinPIE\Tags\Snippet',
        'folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'snippets',
        'realpath check' => true,
      ],
      'PAGE' => [
        'class' => '\igordata\PinPIE\Tags\Page',
        'folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'pages',
        'realpath check' => true,
      ],
      '%' => [
        'class' => '\igordata\PinPIE\Tags\Staticon',
        'folder' => $this->pinpie->root,
        'realpath check' => true,
        'gzip level' => 5,
        'gzip types' => ['js', 'css'],
        'minify types' => ['js', 'css'],
        'minify function' => false,
        'dimensions types' => ['img'],
        'dimensions function' => false,
        'draw function' => false,
      ],
      '=' => ['class' => '\igordata\PinPIE\Tags\Constant'],
      '@' => ['class' => '\igordata\PinPIE\Tags\Command'],
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
    $this->oth = $oth; //you can use that array to store settings for your own scripts
    $this->databases = $databases;
    $this->static_servers = $static_servers;
    $this->showtime = $showtime;
    $this->random_stuff = $random_stuff;
    $this->pinpie = $pinpie;
    $this->debug = $debug;
    $this->tags = $tags;
  }

}


