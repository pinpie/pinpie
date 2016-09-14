<?php

namespace pinpie\pinpie;

class CFG {

  // descriptions are in ReadConf()
  public
    $cache = null,
    $oth = null,
    $static_servers = null,
    $databases = null,
    $showtime = null,
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
    $debug = false; //enables PinPIE::report() output. Use it to enable your own debug mode. Globally available through CFG::$debug.

    //Loading defaults
    $pinpie = [
      'cache class' => false,
      'cache forever time' => PHP_INT_MAX,
      'cache rules' => [
        'default' => ['ignore url' => false, 'ignore query params' => []],
        200 => ['ignore url' => false, 'ignore query params' => []],
        404 => ['ignore url' => true, 'ignore query params' => []]
      ],
      'codepage' => 'utf-8',
      'log' => [
        'path' => 'pin.log',
        'show' => false,
      ],
      'page not found' => 'index.php',
      'route to parent' => 1, //read doc. if exact file not found, instead of 404, PinPIE will try to route request to nearest existing parent entry in url. Default is 1, it means PinPIE will handle "site.com/url" and "site.com/url/" as same page.
      'site url' => $_SERVER['SERVER_NAME'],
      'template clear vars after use' => false,
      'templates folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'templates',
      'template function' => false,
      'templates realpath check' => true,
      'preinclude' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'preinclude.php',
      'postinclude' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'postinclude.php',
    ];

    $tags = [
      '' => [
        'class' => '\pinpie\pinpie\Tags\Chunk',
        'folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'chunks',
        'realpath check' => true,
      ],
      '$' => [
        'class' => '\pinpie\pinpie\Tags\Snippet',
        'folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'snippets',
        'realpath check' => true,
      ],
      'PAGE' => [
        'class' => '\pinpie\pinpie\Tags\Page',
        'folder' => $this->pinpie->root . DIRECTORY_SEPARATOR . 'pages',
        'realpath check' => true,
      ],
      '%' => [
        'class' => '\pinpie\pinpie\Tags\Staticon',
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
      '=' => ['class' => '\pinpie\pinpie\Tags\Constant'],
      '@' => ['class' => '\pinpie\pinpie\Tags\Command'],
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
          'disabled' => '\pinpie\pinpie\Cachers\Disabled',
          'files' => '\pinpie\pinpie\Cachers\Files',
          'APC' => '\pinpie\pinpie\Cachers\APC',
          'Memcache' => '\pinpie\pinpie\Cachers\Memcache',
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
    $this->pinpie = $pinpie;
    $this->debug = $debug;
    $this->tags = $tags;
  }

}


