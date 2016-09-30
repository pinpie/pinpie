<?php

namespace pinpie\pinpie;

use mageekguy\atoum\asserters\variable;
use mageekguy\atoum\php\tokenizer\iterator\value;

class CFG {

  // descriptions are in ReadConf()
  public
    $cache = null,
    $oth = null,
    $static_servers = null,
    $databases = null,
    $showtime = null,
    /** @var \pinpie\pinpie\PP|null */
    $root = null,
    $pinpie = null,
    $debug = null,
    $tags = [];

  public function __construct(\pinpie\pinpie\PP $pp) {
    $this->root = $pp->root;
  }

  public function getDefaults() {
    $cache = []; // settings for current cacher
    $oth = []; //you can put some custom setting here
    $databases = []; //to store database settings
    $static_servers = []; //list here static content servers addresses if you want to use them
    $showtime = false; //show page generating time
    $debug = false; //enables PinPIE::report() output. Use it to enable your own debug mode. Globally available through CFG::$debug.

    //Loading defaults
    $pinpie = [
      'cache class' => '\pinpie\pinpie\Cachers\Files',
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
      'templates clear vars after use' => false,
      'templates folder' => $this->root . DIRECTORY_SEPARATOR . 'templates',
      'templates function' => false,
      'templates realpath check' => true,
      'preinclude' => $this->root . DIRECTORY_SEPARATOR . 'preinclude.php',
      'postinclude' => $this->root . DIRECTORY_SEPARATOR . 'postinclude.php',
    ];

    $tags = [
      '' => [
        'class' => '\pinpie\pinpie\Tags\Chunk',
        'folder' => $this->root . DIRECTORY_SEPARATOR . 'chunks',
        'realpath check' => true,
      ],
      '$' => [
        'class' => '\pinpie\pinpie\Tags\Snippet',
        'folder' => $this->root . DIRECTORY_SEPARATOR . 'snippets',
        'realpath check' => true,
      ],
      'PAGE' => [
        'class' => '\pinpie\pinpie\Tags\Page',
        'folder' => $this->root . DIRECTORY_SEPARATOR . 'pages',
        'realpath check' => true,
      ],
      '%' => [
        'class' => '\pinpie\pinpie\Tags\Staticon',
        'folder' => $this->root,
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
    $arr = [];
    $arr['cache'] = $cache;
    $arr['oth'] = $oth; //you can use that array to store settings for your own scripts
    $arr['databases'] = $databases;
    $arr['static_servers'] = $static_servers;
    $arr['showtime'] = $showtime;
    $arr['pinpie'] = $pinpie;
    $arr['debug'] = $debug;
    $arr['tags'] = $tags;
    return $arr;
  }

  /**
   * Internal method to read configuration file.
   */
  protected function readConfFile($path) {
    $arr = $this->getDefaults();
    $cache = $arr['cache'];
    $oth = $arr['oth'];
    $databases = $arr['databases'];
    $static_servers = $arr['static_servers'];
    $showtime = $arr['showtime'];
    $pinpie = $arr['pinpie'];
    $debug = $arr['debug'];
    $tags = $arr['tags'];
    unset($arr);
    //Reading file and overwriting defaults
    if (file_exists($path)) {
      include($path);
    }
    $arr = [];
    $arr['cache'] = $cache;
    $arr['oth'] = $oth;
    $arr['databases'] = $databases;
    $arr['static_servers'] = $static_servers;
    $arr['showtime'] = $showtime;
    $arr['pinpie'] = $pinpie;
    $arr['debug'] = $debug;
    $arr['tags'] = $tags;
    return $arr;
  }


  public function setSettings($settings) {
    $defaults = $this->getDefaults();
    if (!empty($settings['file'])) {
      $fileconf = $this->readConfFile($settings['file']);
      foreach ($fileconf as $k => $f) {
        if (isset($settings[$k]) AND is_array($settings[$k])) {
          $settings[$k] = array_merge($f, $settings[$k]);
        } else {
          $settings[$k] = $f;
        }
      }
    }
    foreach ($defaults as $k => $d) {
      if (isset($settings[$k]) AND is_array($settings[$k])) {
        $settings[$k] = array_merge($d, $settings[$k]);
      } else {
        $settings[$k] = $d;
      }
    }
    $this->cache = $settings['cache'];
    $this->oth = $settings['oth'];
    $this->databases = $settings['databases'];
    $this->static_servers = $settings['static_servers'];
    $this->showtime = $settings['showtime'];
    $this->pinpie = $settings['pinpie'];
    $this->debug = $settings['debug'];
    $this->tags = $settings['tags'];

  }
}
