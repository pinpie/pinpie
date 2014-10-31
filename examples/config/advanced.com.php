<?php
/*
 * Random sting
 * Random string $random_stuff is used in hashes generation.
 * It MUST be different for every your config.
 * Please, seat at your keyboard now and use the result.
 * Don't forget to do that!
 */
$random_stuff = 'some random string';

/*
 * Site url
 * Used in static uri generation.
 * Can be used in generation of static servers array.
 * Default: $_SERVER['SERVER_NAME']
 */
$pinpie['site url'] = 'site.com';

/*
 * Codepage
 * Define the codepage here and fill free to use in anywhere. See preincludes.php in Examples project.
 */
$pinpie['codepage'] = 'utf-8';

/*
 * Pages folder
 * Path where all pages files are stored.
 * Default: "ROOT/pages".
 */
$pinpie['pages folder'] = ROOT . '/pages';

/*
 * Page not found handler
 * The file in CFG::$pinpie['pages folder'] that will be used when requested page cannot be found.
 */
$pinpie['page not found'] = 'notfound.php';

/*
 * Route to parent directive
 * When requested URL is parsed, the extention ".php" will be added to path and the corresponding file will be included from CFG::$pinpie['pages folder'].
 * Example: If URL is "/some/requested/path", then  CFG::$pinpie['pages folder']."/some/requested/path.php" will be checked.
 * If no such file exist, then PinPIE will try it as a folder and file CFG::$pinpie['pages folder']."/some/requested/path/index.php" will be checked.
 * If no such file will be found, then the last part of URL will be cut off, and the process repeats.
 * This directive allow you to control behavior of parser and how many times the cut-and-check process will be repeated.
 * Use some big value to route all requests like "handler/some/very/long/r/e/q/u/e/s/t/" to a "handler.php" file.
 * Default: 1
 * Default 1 means PinPIE will handle "site.com/url" and "site.com/url/" as same page.
 */
$pinpie['route to parent'] = 99;

/*
 * Cache rules
 * There are some rules affecting hash generation. You have to read about that, to prevent your cache be bloated and grow too fast.
 */

/*
 * Cache type
 * This defines what cache class file will be used.
 * There are three cache classes available now:
 *  - disabled - a fake cache
 *  - filecache - fastest if there is enough RAM caching, using files. The OS will handle all IO itself, caching all in free RAM (yellow bar in htop at Linux). So until you have free RAM, it is the faster way.
 *  - memcached - using fast and distributed well-known Memcached caching system. Cache servers have to be defined.
 */
$pinpie['cache type'] = 'memcached';

/*
 * Cache servers
 * Array of cache servers, used in cache.class.memcache.php file.
 */
$pinpie['cache servers'] = [
  [
    'host' => 'unix:///tmp/memcached.sock',
    'port' => 0
  ],
];

/*
 * Static folder
 * By default when static tags are processed, files will be looked for inside the ROOT folder. You can set here different path, where static content is stored.
 */
$pinpie['static folder'] = '/app/static';

/*
 * Function to be used to minify static files
 * By default is FALSE and will not be called.
 * If you want minify your static files like css or js automaticaly, you have to write a function and provide here its name.
 */
$pinpie['auto minify static files function'] = 'autominify';

/*
 * Minify static filetypes
 * Filetypes (defined by tag) to apply to be minified.
 * Default:  ['js', 'css']
 */
$pinpie['minify static filetypes'] = ['js', 'css'];

/*
 * Gzip static files
 * Shoul be used with nginx gzip_static or similar.
 * If TRUE than PinPIE will check existence of .gz version of static file, if it's type (not extension) defined by the tag is allowed to be compressed. If this file is older than original, or does not exist - then new file will be created.
 * Default: FALSE.
 */
$pinpie['gzip static'] = true;

/*
 * Level of gzip compression
 * Default: 9.
 */
$pinpie['gzip static level'] = 9;

/*
 * Gzip static filetypes
 * Filetypes (defined by tag) to apply gzip compression to.
 * Default:  ['js', 'css']
 */
$pinpie['gzip static filetypes'] = ['js', 'css'];

/*
 * Static servers
 * Array of static serves used to provide static files in more simultaneous threads.
 */
$static_servers = [
  's0.' . $pinpie['site url'],
  's1.' . $pinpie['site url'],
  's2.' . $pinpie['site url'],
];

/*
 * $databases variable
 * It's accessible through CFG::$databases, so you can store here database settings and use them anywhere.
 */
$databases['main'] = [
  'host' => 'localhost',
  'dbname' => 'database',
  'login' => 'login',
  'password' => 'password',
  'port' => 3306,
  'socket' => null,
  'codepage' => 'utf8'
];

/*
 * Custom settings variable
 * It's accessible through CFG::$conf. You can store here any config settings you want.
 */
$conf['any settings'] = 'some';


$pinpie['cache rules'][200]['ignore query params'][] = 'XDEBUG_SESSION_START';